@extends('admin.layout')

@section('title', 'Record Office Expense | ARTSCI Finance')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 rounded-lg border border-orange-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-orange-600">Office Expense</div>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-950">Record Office Expense</h1>
            <p class="mt-1 text-sm text-gray-600">Record a general company overhead cost — diesel, salary, rent, utilities, and similar recurring office costs.</p>
        </div>

        <form method="POST" action="{{ route('finance.office-expenses.store') }}" enctype="multipart/form-data">
            @csrf
            @include('finance.office-expenses._form')
        </form>
    </div>
</div>
@endsection
