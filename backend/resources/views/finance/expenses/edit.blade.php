@extends('admin.layout')

@section('title', 'Edit Expense | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Expense</h1>
            <p class="text-sm text-gray-600 mt-1">Only pending expenses can be edited.</p>
        </div>

        <form method="POST" action="{{ route('finance.expenses.update', $expense) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('finance.expenses._form')
        </form>
    </div>
</div>
@endsection
