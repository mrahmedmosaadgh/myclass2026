// In your DataTableV7 component props definition
const props = defineProps({
    baseUrl: {
        type: String,
        required: true
    },
    items: {
        type: Array,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    pagination: {
        type: Array,
        default: () => []
    },
    isSearching: {
        type: Boolean,
        default: false
    },
    searchQuery: {
        type: String,
        default: ''
    },
    isFiltered: {
        type: Boolean,
        default: false
    },
    importColumns: {
        type: Array,
        default: () => []
    },
    actions: {
        type: Array,
        default: () => []
    }
});