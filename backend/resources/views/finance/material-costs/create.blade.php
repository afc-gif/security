@extends('admin.layout')

@section('title', 'Record Material Cost | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Material Entry</div>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-950">Record Material Cost</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $project->project_code }} · {{ $project->title }}</p>
        </div>

        <form method="POST" action="{{ route('finance.material-costs.store', $project) }}" enctype="multipart/form-data">
            @csrf
            @include('finance.material-costs._form', ['project' => $project])
        </form>
    </div>
</div>
@endsection
