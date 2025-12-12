<!-- <AdvancedEmojiPicker @select="handleEmojiSelect" /> -->
<!-- import AdvancedEmojiPicker from '@/Components/Common/AdvancedEmojiPicker.vue'; -->

<template>
    <div>
      <h2 class="text-lg mb-2">Select an emoji:</h2>

      <!-- Emoji Picker Component -->
      <div class="emoji-picker p-2 bg-white border rounded shadow-md max-w-xs">
        <!-- Search Bar -->
        <input
          v-model="searchQuery"
          class="w-full p-2 border rounded mb-2"
          placeholder="Search Emojis..."
        />

        <!-- Emoji Category Tabs -->
        <div class="flex space-x-2 mb-2">
          <button
            v-for="(category, index) in emojiCategories"
            :key="index"
            :class="['px-2 py-1 rounded', selectedCategory === category ? 'bg-blue-500 text-white' : 'bg-gray-200']"
            @click="changeCategory(category)"
          >
            {{ category }}
          </button>
        </div>

        <!-- Emoji Grid -->
        <div class="grid grid-cols-6 gap-2 text-2xl cursor-pointer">
          <span
            v-for="(emoji, index) in filteredEmojis"
            :key="index"
            @click="selectEmoji(emoji)"
            class="hover:scale-110 transition-transform"
          >
            {{ emoji.symbol }}
          </span>
        </div>
      </div>

      <!-- Recently Used Emojis -->
      <div v-if="recentlyUsed.length" class="mt-4">
        <h3 class="text-lg mb-2">Recently Used:</h3>
        <div class="flex space-x-2">
          <span
            v-for="(emoji, index) in recentlyUsed"
            :key="index"
            @click="selectEmoji(emoji)"
            class="text-2xl cursor-pointer"
          >
            {{ emoji.symbol }}
          </span>
        </div>
      </div>

      <!-- Selected Emoji -->
      <div class="mt-4 text-xl">
        Selected: <span>{{ selectedEmoji }}</span>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'EmojiPicker',
    data() {
      return {
        searchQuery: '', // For search filter
        selectedCategory: 'All', // Default category
        selectedEmoji: '', // Holds selected emoji
        emojis: [
          // Add a "category" key for each emoji
          { symbol: '✅', category: 'Basic' },
          { symbol: '❌', category: 'Basic' },
          { symbol: '🚀', category: 'Objects' },
          { symbol: '🔥', category: 'Objects' },
          { symbol: '🎯', category: 'Objects' },
          { symbol: '🎉', category: 'Objects' },
          { symbol: '📚', category: 'Symbols' },
          { symbol: '📝', category: 'Symbols' },
          { symbol: '❤️', category: 'Emotions' },
          { symbol: '👍', category: 'Emotions' },
          { symbol: '👎', category: 'Emotions' },
          { symbol: '👨‍🏫', category: 'People' },
          { symbol: '👩‍🏫', category: 'People' },
          { symbol: '👥', category: 'People' },
          { symbol: '📅', category: 'Objects' },
          { symbol: '💡', category: 'Objects' },
          { symbol: '🔄', category: 'Objects' },
          { symbol: '⚠️', category: 'Symbols' },
          { symbol: '🏫', category: 'Objects' },
          { symbol: '🤖', category: 'Objects' },
        //   [
  // People & Body
  { symbol: '👩‍🦱', category: 'People & Body' },
  { symbol: '👨‍🦰', category: 'People & Body' },
  { symbol: '👩‍🦳', category: 'People & Body' },
  { symbol: '👩‍🦴', category: 'People & Body' },
  { symbol: '👴', category: 'People & Body' },
  { symbol: '👵', category: 'People & Body' },
  { symbol: '🧓', category: 'People & Body' },
  { symbol: '👶', category: 'People & Body' },
  { symbol: '🧑‍🦳', category: 'People & Body' },
  { symbol: '👱‍♂️', category: 'People & Body' },
  { symbol: '👱‍♀️', category: 'People & Body' },

  // Nature
  { symbol: '🌸', category: 'Nature' },
  { symbol: '🌻', category: 'Nature' },
  { symbol: '🌞', category: 'Nature' },
  { symbol: '🌲', category: 'Nature' },
  { symbol: '🌳', category: 'Nature' },
  { symbol: '🌵', category: 'Nature' },
  { symbol: '🌾', category: 'Nature' },
  { symbol: '🍁', category: 'Nature' },
  { symbol: '🍂', category: 'Nature' },
  { symbol: '🍃', category: 'Nature' },
  { symbol: '🍄', category: 'Nature' },

  // Animals
  { symbol: '🐶', category: 'Animals' },
  { symbol: '🐱', category: 'Animals' },
  { symbol: '🐭', category: 'Animals' },
  { symbol: '🐹', category: 'Animals' },
  { symbol: '🐰', category: 'Animals' },
  { symbol: '🦊', category: 'Animals' },
  { symbol: '🐻', category: 'Animals' },
  { symbol: '🐼', category: 'Animals' },
  { symbol: '🦁', category: 'Animals' },
  { symbol: '🐯', category: 'Animals' },
  { symbol: '🐨', category: 'Animals' },

  // Food & Drink
  { symbol: '🍏', category: 'Food & Drink' },
  { symbol: '🍎', category: 'Food & Drink' },
  { symbol: '🍐', category: 'Food & Drink' },
  { symbol: '🍊', category: 'Food & Drink' },
  { symbol: '🍋', category: 'Food & Drink' },
  { symbol: '🍌', category: 'Food & Drink' },
  { symbol: '🍍', category: 'Food & Drink' },
  { symbol: '🍒', category: 'Food & Drink' },
  { symbol: '🍓', category: 'Food & Drink' },
  { symbol: '🍅', category: 'Food & Drink' },
  { symbol: '🥥', category: 'Food & Drink' },

  // Activities & Objects
  { symbol: '⚽', category: 'Activities & Objects' },
  { symbol: '🏀', category: 'Activities & Objects' },
  { symbol: '🎱', category: 'Activities & Objects' },
  { symbol: '🏅', category: 'Activities & Objects' },
  { symbol: '🎮', category: 'Activities & Objects' },
  { symbol: '🎤', category: 'Activities & Objects' },
  { symbol: '🎬', category: 'Activities & Objects' },
  { symbol: '🎧', category: 'Activities & Objects' },
  { symbol: '🎻', category: 'Activities & Objects' },
  { symbol: '📱', category: 'Activities & Objects' },
  { symbol: '💻', category: 'Activities & Objects' },

  // Travel & Places
  { symbol: '🏝️', category: 'Travel & Places' },
  { symbol: '🏖️', category: 'Travel & Places' },
  { symbol: '🏙️', category: 'Travel & Places' },
  { symbol: '🏜️', category: 'Travel & Places' },
  { symbol: '🌍', category: 'Travel & Places' },
  { symbol: '🌎', category: 'Travel & Places' },
  { symbol: '🌏', category: 'Travel & Places' },
  { symbol: '🌁', category: 'Travel & Places' },
  { symbol: '🏰', category: 'Travel & Places' },
  { symbol: '🗽', category: 'Travel & Places' },
  { symbol: '🏕️', category: 'Travel & Places' },

  // Flags
  { symbol: '🇺🇸', category: 'Flags' },
  { symbol: '🇨🇦', category: 'Flags' },
  { symbol: '🇮🇳', category: 'Flags' },
  { symbol: '🇬🇧', category: 'Flags' },
  { symbol: '🇯🇵', category: 'Flags' },
  { symbol: '🇩🇪', category: 'Flags' },
  { symbol: '🇧🇷', category: 'Flags' },
  { symbol: '🇮🇹', category: 'Flags' },
  { symbol: '🇫🇷', category: 'Flags' },
  { symbol: '🇨🇳', category: 'Flags' },
  { symbol: '🇲🇽', category: 'Flags' },

  // Symbols
  { symbol: '❤️', category: 'Symbols' },
  { symbol: '💔', category: 'Symbols' },
  { symbol: '🔔', category: 'Symbols' },
  { symbol: '🔑', category: 'Symbols' },
  { symbol: '🔒', category: 'Symbols' },
  { symbol: '💎', category: 'Symbols' },
  { symbol: '💡', category: 'Symbols' },
  { symbol: '🔦', category: 'Symbols' },
  { symbol: '⚡', category: 'Symbols' },
  { symbol: '🔆', category: 'Symbols' },
  { symbol: '💰', category: 'Symbols' },

  // Weather
  { symbol: '🌞', category: 'Weather' },
  { symbol: '⛅', category: 'Weather' },
  { symbol: '🌧️', category: 'Weather' },
  { symbol: '🌩️', category: 'Weather' },
  { symbol: '🌨️', category: 'Weather' },
  { symbol: '🌪️', category: 'Weather' },
  { symbol: '🌫️', category: 'Weather' },
  { symbol: '🌬️', category: 'Weather' },
  { symbol: '❄️', category: 'Weather' },
  { symbol: '🌈', category: 'Weather' },
  { symbol: '☁️', category: 'Weather' }


        ],
        emojiCategories: ['All', 'Basic', 'Objects', 'Symbols', 'Emotions', 'People'], // Available categories
        recentlyUsed: JSON.parse(localStorage.getItem('recentlyUsed')) || [], // Get recently used from localStorage
      };
    },
    computed: {
      filteredEmojis() {
        // Filter by category and search query
        return this.emojis.filter(emoji => {
          const matchesCategory = this.selectedCategory === 'All' || emoji.category === this.selectedCategory;
          const matchesSearch = emoji.symbol.toLowerCase().includes(this.searchQuery.toLowerCase());
          return matchesCategory && matchesSearch;
        });
      }
    },
    methods: {
      selectEmoji(emoji) {
        // Add to recently used
        this.addToRecentlyUsed(emoji);

        // Emit selected emoji
        this.$emit('select', emoji.symbol);

        // Set selected emoji
        this.selectedEmoji = emoji.symbol;
      },
      changeCategory(category) {
        this.selectedCategory = category; // Change selected category
      },
      addToRecentlyUsed(emoji) {
        // Avoid duplicates in the recently used array
        if (!this.recentlyUsed.some(item => item.symbol === emoji.symbol)) {
          this.recentlyUsed.unshift(emoji); // Add at the start
          if (this.recentlyUsed.length > 10) this.recentlyUsed.pop(); // Limit to 10
          localStorage.setItem('recentlyUsed', JSON.stringify(this.recentlyUsed)); // Save to localStorage
        }
      }
    }
  };
  </script>

  <style scoped>
  .emoji-picker {
    z-index: 1000;
    max-height: 400px;
    overflow-y: auto;
  }
  </style>

