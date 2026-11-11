'use strict';

const searchInput = document.querySelector('#server-search');

if (searchInput) {
    const suggestions = document.querySelector('#server-suggestions');
    const searchForm = searchInput.closest('form');
    let debounceTimer;
    let activeIndex = -1;
    let requestController;

    const closeSuggestions = () => {
        suggestions.replaceChildren();
        suggestions.hidden = true;
        searchInput.setAttribute('aria-expanded', 'false');
        searchInput.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const chooseSuggestion = (value) => {
        searchInput.value = value;
        closeSuggestions();
        searchForm.requestSubmit();
    };

    const setActiveSuggestion = (index) => {
        const options = suggestions.querySelectorAll('[role="option"]');
        if (options.length === 0) {
            return;
        }

        activeIndex = (index + options.length) % options.length;
        options.forEach((option, optionIndex) => {
            const isActive = optionIndex === activeIndex;
            option.setAttribute('aria-selected', String(isActive));
            if (isActive) {
                searchInput.setAttribute('aria-activedescendant', option.id);
                option.scrollIntoView({ block: 'nearest' });
            }
        });
    };

    searchInput.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        if (requestController) {
            requestController.abort();
        }
        closeSuggestions();

        const query = searchInput.value.trim();
        if (query === '') {
            return;
        }

        debounceTimer = window.setTimeout(async () => {
            requestController = new AbortController();
            const url = new URL(searchInput.dataset.suggestionsUrl, window.location.href);
            url.searchParams.set('q', query);

            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    signal: requestController.signal,
                });
                if (!response.ok) {
                    throw new Error(`Suggestions request failed (${response.status}).`);
                }

                const names = await response.json();
                if (!Array.isArray(names) || searchInput.value.trim() !== query) {
                    return;
                }

                suggestions.replaceChildren();
                names.forEach((name, index) => {
                    if (typeof name !== 'string') {
                        return;
                    }

                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'list-group-item list-group-item-action';
                    option.id = `server-suggestion-${index}`;
                    option.setAttribute('role', 'option');
                    option.setAttribute('aria-selected', 'false');
                    option.textContent = name;
                    option.addEventListener('click', () => chooseSuggestion(name));
                    suggestions.append(option);
                });

                if (suggestions.childElementCount > 0) {
                    suggestions.hidden = false;
                    searchInput.setAttribute('aria-expanded', 'true');
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            }
        }, 180);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && !suggestions.hidden) {
            event.preventDefault();
            setActiveSuggestion(activeIndex + 1);
        } else if (event.key === 'ArrowUp' && !suggestions.hidden) {
            event.preventDefault();
            setActiveSuggestion(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0 && !suggestions.hidden) {
            event.preventDefault();
            const activeOption = suggestions.querySelectorAll('[role="option"]')[activeIndex];
            if (activeOption) {
                chooseSuggestion(activeOption.textContent);
            }
        } else if (event.key === 'Escape') {
            closeSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!searchInput.parentElement.contains(event.target)) {
            closeSuggestions();
        }
    });
}
