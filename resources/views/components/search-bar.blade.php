<div class="searchbar-isolated">
    <form action="{{ route('games.search') }}" method="GET">
        <label for="search">Search for a game</label>
        <input id="search" type="text" name="q" value="{{ $query ?? '' }}">
        <button type="submit">Search</button>
    </form>
</div>
