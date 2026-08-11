@extends('admin.layout')

@section('title', 'Edit Material Cost | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Material Cost</h1>
            <p class="text-sm text-gray-600 mt-1">Only pending material costs can be edited.</p>
        </div>

        <form method="POST" action="{{ route('finance.material-costs.update', $materialCost) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('finance.material-costs._form', ['project' => $materialCost->project])
        </form>
    </div>
</div>
@endsection
