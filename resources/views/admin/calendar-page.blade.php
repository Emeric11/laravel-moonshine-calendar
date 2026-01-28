@extends('moonshine::layouts.app')

@section('content')
    <div class="moonshine-card">
        <iframe 
            src="/calendar" 
            style="width: 100%; height: calc(100vh - 200px); border: none; border-radius: 8px;"
            title="Calendario de Producción">
        </iframe>
    </div>
@endsection
