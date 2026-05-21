<div class="search-wrap">
    <form action="{{ route('games.search') }}" method="GET" class="search-form">

        <label for="search">Search for a game</label>

        <input
            id="search"
            type="search"
            name="q"
            value="{{ $query ?? '' }}"
            placeholder="Search for a game..."
            required
            autocomplete="off"
        >
        <button type="button" class="clear-btn" aria-label="Clear search">
            ✕
        </button>
        <button type="submit">Search</button>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('.search-form');
    if (!form) return;

    const input = form.querySelector('input');
    const clearBtn = form.querySelector('.clear-btn');

    if (!input || !clearBtn) return;

    function update() {
        if (input.value.trim().length > 0) {
            form.classList.add('has-value');
        } else {
            form.classList.remove('has-value');
        }
    }

    input.addEventListener('input', update);

    clearBtn.addEventListener('click', () => {
        input.value = '';
        input.focus();
        update();
    });

    update(); // IMPORTANT (Laravel prefilled value)
});
</script>
