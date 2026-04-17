<nav class="bottom-nav" aria-label="Field navigation">
    <a class="{{ request()->routeIs('field.dashboard') ? 'active' : '' }}" href="{{ route('field.dashboard') }}" @if(request()->routeIs('field.dashboard')) aria-current="page" @endif>
        <b>DB</b>
        <span>Home</span>
    </a>
    <a class="{{ request()->routeIs('field.jobs.*') ? 'active' : '' }}" href="{{ route('field.jobs.index') }}" @if(request()->routeIs('field.jobs.*')) aria-current="page" @endif>
        <b>JB</b>
        <span>Jobs</span>
    </a>
    <a class="{{ request()->routeIs('field.projects.*') ? 'active' : '' }}" href="{{ route('field.projects.index') }}" @if(request()->routeIs('field.projects.*')) aria-current="page" @endif>
        <b>PR</b>
        <span>Projects</span>
    </a>
    <a class="{{ request()->routeIs('field.tasks.*') ? 'active' : '' }}" href="{{ route('field.tasks.index') }}" @if(request()->routeIs('field.tasks.*')) aria-current="page" @endif>
        <b>TK</b>
        <span>Tasks</span>
    </a>
    <a class="{{ request()->routeIs('field.inspections.*') ? 'active' : '' }}" href="{{ route('field.inspections.index') }}" @if(request()->routeIs('field.inspections.*')) aria-current="page" @endif>
        <b>IN</b>
        <span>Inspect</span>
    </a>
</nav>
