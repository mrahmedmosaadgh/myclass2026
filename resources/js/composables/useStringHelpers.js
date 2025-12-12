export function useStringHelpers() {
    const capitalizeFirst = (str) => {
        if (!str || typeof str !== 'string') return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    };

    const capitalizeWords = (str) => {
        if (!str || typeof str !== 'string') return '';
        return str
            .split(' ')
            .map(word => capitalizeFirst(word))
            .join(' ');
    };

    const toKebabCase = (str) => {
        return str && typeof str === 'string'
            ? str.trim().toLowerCase().replace(/\s+/g, '-')
            : '';
    };

    return {
        capitalizeFirst,
        capitalizeWords,
        toKebabCase,
    };
}
