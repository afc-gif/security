<nav class="bottom-nav" aria-label="Field navigation">
    <a class="{{ request()->routeIs('field.dashboard') ? 'active' : '' }}" href="{{ route('field.dashboard') }}" @if(request()->routeIs('field.dashboard')) aria-current="page" @endif>
        <b>DB</b>
        <span>Dashboard</span>
    </a>
    <a class="{{ request()->routeIs('field.jobs.*') ? 'active' : '' }}" href="{{ route('field.jobs.index') }}" @if(request()->routeIs('field.jobs.*')) aria-current="page" @endif>
        <b>JB</b>
        <span>Jobs</span>
    </a>
    <a class="{{ request()->routeIs('field.projects.*') ? 'active' : '' }}" href="{{ route('field.projects.index') }}" @if(request()->routeIs('field.projects.*')) aria-current="page" @endif>
        <b>PR</b>
        <span>Projects</span>
    </a>
    {{-- Legacy Tasks and Inspections remain accessible by route but are hidden from the main field workflow navigation. --}}
</nav>
