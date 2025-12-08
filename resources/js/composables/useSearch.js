import { ref, watch } from 'vue';
import { debounce } from 'lodash';

export function useSearch(callback, delay = 300) {
    const searchQuery = ref('');
    const isSearching = ref(false);

    const debouncedSearch = debounce(async (query) => {
        if (!query.trim()) {
            isSearching.value = false;
            return;
        }

        isSearching.value = true;
        try {
            await callback(query);
        } finally {
            isSearching.value = false;
        }
    }, delay);

    watch(searchQuery, (newQuery) => {
        debouncedSearch(newQuery);
    });

    return {
        searchQuery,
        isSearching,
        clearSearch: () => {
            searchQuery.value = '';
            isSearching.value = false;
        }
    };
}