@extends('admin.layout')

@section('title', 'Record Material Cost | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Record Material Cost</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $project->project_code }} · {{ $project->title }}</p>
        </div>

        <form method="POST" action="{{ route('finance.material-costs.store', $project) }}" enctype="multipart/form-data">
            @csrf
            @include('finance.material-costs._form', ['project' => $project])
        </form>
    </div>
</div>
@endsection
