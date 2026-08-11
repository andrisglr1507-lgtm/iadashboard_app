<?php

$modules = [
    'Branch' => [
        'model' => 'Branch',
        'route' => 'sodc.branches',
        'view_path' => 'sodc.master.branches',
        'dir' => 'resources/views/sodc/master/branches',
        'fields' => [
            ['name' => 'branch_code', 'label' => 'Kode Cabang', 'type' => 'text'],
            ['name' => 'branch_name', 'label' => 'Nama Cabang', 'type' => 'text'],
        ]
    ],
    'Warehouse' => [
        'model' => 'Warehouse',
        'route' => 'sodc.warehouses',
        'view_path' => 'sodc.master.warehouses',
        'dir' => 'resources/views/sodc/master/warehouses',
        'fields' => [
            ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'select_branch'],
            ['name' => 'warehouse_code', 'label' => 'Kode Gudang', 'type' => 'text'],
            ['name' => 'warehouse_name', 'label' => 'Nama Gudang', 'type' => 'text'],
        ]
    ],
    'Bin' => [
        'model' => 'Bin',
        'route' => 'sodc.bins',
        'view_path' => 'sodc.master.bins',
        'dir' => 'resources/views/sodc/master/bins',
        'fields' => [
            ['name' => 'warehouse_id', 'label' => 'Gudang', 'type' => 'select_warehouse'],
            ['name' => 'bin_code', 'label' => 'Kode Bin', 'type' => 'text'],
            ['name' => 'zone', 'label' => 'Zona', 'type' => 'text'],
        ]
    ],
    'Product' => [
        'model' => 'Product',
        'route' => 'sodc.products',
        'view_path' => 'sodc.master.products',
        'dir' => 'resources/views/sodc/master/products',
        'fields' => [
            ['name' => 'sku_code', 'label' => 'SKU Code', 'type' => 'text'],
            ['name' => 'sku_name', 'label' => 'SKU Name', 'type' => 'text'],
            ['name' => 'barcode', 'label' => 'Barcode', 'type' => 'text'],
        ]
    ],
    'Team' => [
        'model' => 'OpnameTeam',
        'route' => 'sodc.teams',
        'view_path' => 'sodc.master.teams',
        'dir' => 'resources/views/sodc/master/teams',
        'fields' => [
            ['name' => 'team_code', 'label' => 'Team Code', 'type' => 'text'],
            ['name' => 'team_name', 'label' => 'Team Name', 'type' => 'text'],
            ['name' => 'team_type', 'label' => 'Type (COUNTING/RECOUNT)', 'type' => 'text'],
        ]
    ]
];

$controllerTemplate = <<<PHP
<?php

namespace App\Http\Controllers\Sodc\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ModelName};

class {ModuleName}Controller extends Controller
{
    public function index()
    {
        \$data = {ModelName}::orderBy('id', 'desc')->get();
        return view('{ViewPath}.index', compact('data'));
    }

    public function create()
    {
        return view('{ViewPath}.create');
    }

    public function store(Request \$request)
    {
        {ModelName}::create(\$request->all());
        return redirect()->route('{RouteName}.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(\$id)
    {
        \$item = {ModelName}::findOrFail(\$id);
        return view('{ViewPath}.edit', compact('item'));
    }

    public function update(Request \$request, \$id)
    {
        \$item = {ModelName}::findOrFail(\$id);
        \$item->update(\$request->all());
        return redirect()->route('{RouteName}.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(\$id)
    {
        {ModelName}::findOrFail(\$id)->delete();
        return redirect()->route('{RouteName}.index')->with('success', 'Data berhasil dihapus');
    }
}
PHP;

$indexTemplate = <<<BLADE
@extends('layouts.app')
@section('title', '{ModuleName}')
@section('page_title', 'Master {ModuleName}')

@section('page_actions')
<a href="{{ route('{RouteName}.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none;">
    <i class="fas fa-plus"></i> Tambah {ModuleName}
</a>
@endsection

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div style="margin-top: 20px; padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <table class="premium-table" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr>
{TableHeaders}
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\$data as \$row)
            <tr>
{TableCells}
                <td>
                    <a href="{{ route('{RouteName}.edit', \$row->id) }}" style="color: #0284c7; margin-right: 10px;"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('{RouteName}.destroy', \$row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer;"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
BLADE;

$formTemplate = <<<BLADE
@extends('layouts.app')
@section('title', '{Action} {ModuleName}')
@section('page_title', '{Action} {ModuleName}')

@section('page_actions')
<a href="{{ route('{RouteName}.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset(\$item) ? route('{RouteName}.update', \$item->id) : route('{RouteName}.store') }}" method="POST">
        @csrf
        @if(isset(\$item)) @method('PUT') @endif

{FormInputs}
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection
BLADE;


@mkdir('app/Http/Controllers/Sodc/Master', 0755, true);

foreach($modules as $modName => $modConf) {
    // Generate Controller
    $ctrl = str_replace(
        ['{ModelName}', '{ModuleName}', '{RouteName}', '{ViewPath}'],
        [$modConf['model'], $modName, $modConf['route'], $modConf['view_path']],
        $controllerTemplate
    );
    file_put_contents("app/Http/Controllers/Sodc/Master/{$modName}Controller.php", $ctrl);

    // Generate Views
    @mkdir($modConf['dir'], 0755, true);
    
    // Index
    $headers = "";
    $cells = "";
    $inputs = "";
    foreach($modConf['fields'] as $f) {
        $headers .= "                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>{$f['label']}</th>\n";
        $cells .= "                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ \$row->{$f['name']} }}</td>\n";
        
        $inputs .= "        <div style='margin-bottom: 15px;'>\n";
        $inputs .= "            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>{$f['label']}</label>\n";
        $inputs .= "            <input type='text' name='{$f['name']}' value=\"{{ \$item->{$f['name']} ?? '' }}\" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>\n";
        $inputs .= "        </div>\n";
    }

    $index = str_replace(
        ['{ModuleName}', '{RouteName}', '{TableHeaders}', '{TableCells}'],
        [$modName, $modConf['route'], rtrim($headers), rtrim($cells)],
        $indexTemplate
    );
    file_put_contents("{$modConf['dir']}/index.blade.php", $index);

    // Create
    $create = str_replace(
        ['{ModuleName}', '{RouteName}', '{Action}', '{FormInputs}'],
        [$modName, $modConf['route'], 'Tambah', rtrim($inputs)],
        $formTemplate
    );
    file_put_contents("{$modConf['dir']}/create.blade.php", $create);

    // Edit
    $edit = str_replace(
        ['{ModuleName}', '{RouteName}', '{Action}', '{FormInputs}'],
        [$modName, $modConf['route'], 'Edit', rtrim($inputs)],
        $formTemplate
    );
    file_put_contents("{$modConf['dir']}/edit.blade.php", $edit);
}

echo "CRUD Generated successfully!";
