<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        $vendas = Venda::with('produtos')
            ->where('cliente_id', Auth::id())
            ->latest()
            ->get();

        return view('compras.index', compact('vendas'));
    }

    public function create()
    {
        $produtos = Produto::orderBy('nome')->get();

        return view('compras.create', compact('produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'itens' => 'required|array',
            'itens.*' => 'nullable|integer|min:0',
        ]);

        $itens = collect($request->input('itens'))
            ->map(fn ($qtd) => (int) $qtd)
            ->filter(fn ($qtd) => $qtd > 0);

        if ($itens->isEmpty()) {
            return back()->withInput()->withErrors(['itens' => 'Informe a quantidade de pelo menos um produto.']);
        }

        $produtos = Produto::whereIn('id', $itens->keys())->get()->keyBy('id');

        foreach ($itens as $id => $qtd) {
            $produto = $produtos->get($id);

            if (! $produto) {
                return back()->withInput()->withErrors(['itens' => 'Produto inválido na compra.']);
            }

            if ($qtd > $produto->estoque) {
                return back()->withInput()->withErrors([
                    'itens' => "Estoque insuficiente para {$produto->nome} (disponível: {$produto->estoque}).",
                ]);
            }
        }

        DB::transaction(function () use ($itens, $produtos) {
            $venda = Venda::create(['cliente_id' => Auth::id()]);

            foreach ($itens as $id => $qtd) {
                $produto = $produtos->get($id);

                $venda->produtos()->attach($produto->id, [
                    'quantidade' => $qtd,
                    'preco_unitario' => $produto->preco,
                ]);

                $produto->decrement('estoque', $qtd);
            }
        });

        return redirect()->route('compras.index')->with('sucesso', 'Compra realizada!');
    }
}
