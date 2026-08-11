@extends('admin.layout')

@section('title', 'Record Expense | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-blue-700">Expense Entry</div>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-950">Record Expense</h1>
            <p class="mt-1 text-sm text-gray-600">Choose where the spending belongs, enter the amount, and attach the receipt if available.</p>
        </div>

        <form method="POST" action="{{ route('finance.expenses.store') }}" enctype="multipart/form-data">
            @csrf
            @include('finance.expenses._form')
        </form>
    </div>
</div>
@endsection
