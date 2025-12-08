<template>
    <div class="data-table-display">
      <h3 v-if="title">{{ title }}</h3>

      <div class="primitive-properties">
        <div v-for="(value, key) in primitiveProperties" :key="key" class="property-row">
          <div class="property-key">{{ formatHeader(key) }}:</div>
          <div class="property-value">{{ formatValue(value) }}</div>
        </div>
      </div>

      <div v-for="(array, key) in arrayProperties" :key="key" class="array-section">
        <h4>{{ formatHeader(key) }} <span class="array-count">({{ array.length }} items)</span></h4>
        <div v-if="array.length === 0" class="empty-message">
          No {{ formatHeader(key) }} data available.
        </div>
        <table v-else class="data-table">
          <thead>
            <tr>
              <th v-for="(header, index) in getTableHeaders(array)" :key="index">
                {{ formatHeader(header) }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, itemIndex) in array" :key="itemIndex">
              <td v-for="(header, headerIndex) in getTableHeaders(array)" :key="headerIndex">
                {{ formatValue(getNestedValue(item, header)) }}
                </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-for="(obj, key) in objectProperties" :key="'obj-' + key" class="object-section">
           <h4>{{ formatHeader(key) }}</h4>
           <div class="primitive-properties nested">
               <div v-for="(nestedValue, nestedKey) in getPrimitiveProperties(obj)" :key="nestedKey" class="property-row">
                  <div class="property-key">{{ formatHeader(nestedKey) }}:</div>
                  <div class="property-value">{{ formatValue(nestedValue) }}</div>
               </div>
           </div>
           <div v-if="!Object.keys(getPrimitiveProperties(obj)).length" class="empty-message">
             (Nested Object - No direct primitive properties shown)
           </div>
      </div>

    </div>
  </template>

  <script>
  export default {
    name: 'DataTableDisplay',
    props: {
      /**
       * The object data to display in a table-like structure.
       * Can be a simple object or a nested structure.
       */
      dataObject: {
        type: Object,
        required: true,
      },
      /**
       * Optional title for the main section.
       */
      title: {
        type: String,
        default: '',
      },
      /**
       * Array of keys to EXCLUDE from the display.
       * Use this to hide technical fields like 'created_at', 'updated_at', 'deleted_at', 'data', 'id'.
       */
      excludeKeys: {
        type: Array,
        default: () => ['created_at', 'updated_at', 'deleted_at', 'data', 'pivot', 'id'],
      },
      /**
       * Optional mapping for translating keys into human-readable headers.
       * Example: { 'user_role': 'User Role', 'h_r_id': 'HR ID' }
       */
      keyMap: {
        type: Object,
        default: () => ({}),
      },
    },
    computed: {
      /**
       * Filters dataObject keys into primitive properties.
       */
      primitiveProperties() {
        const primitives = {};
        for (const key in this.dataObject) {
          if (this.dataObject.hasOwnProperty(key) && !this.excludeKeys.includes(key)) {
            const value = this.dataObject[key];
            if (typeof value !== 'object' && !Array.isArray(value) && value !== null) {
               primitives[key] = value;
            } else if (value === null) { // Explicitly include nulls from the top level
               primitives[key] = null;
            }
          }
        }
        return primitives;
      },
      /**
       * Filters dataObject keys into array properties.
       */
      arrayProperties() {
        const arrays = {};
        for (const key in this.dataObject) {
          if (this.dataObject.hasOwnProperty(key) && !this.excludeKeys.includes(key)) {
            const value = this.dataObject[key];
            if (Array.isArray(value)) {
              arrays[key] = value;
            }
          }
        }
        return arrays;
      },
       /**
       * Filters dataObject keys into object properties (excluding arrays and nulls).
       */
      objectProperties() {
         const objects = {};
         for (const key in this.dataObject) {
           if (this.dataObject.hasOwnProperty(key) && !this.excludeKeys.includes(key)) {
             const value = this.dataObject[key];
             // Check if it's an object, not an array, and not null
             if (typeof value === 'object' && !Array.isArray(value) && value !== null) {
               objects[key] = value;
             }
           }
         }
         return objects;
      }
    },
    methods: {
       /**
        * Gets primitive properties from a nested object.
        * Helps display direct key-values of nested objects.
        */
       getPrimitiveProperties(obj) {
         if (typeof obj !== 'object' || obj === null || Array.isArray(obj)) return {};
         const primitives = {};
         for (const key in obj) {
           if (obj.hasOwnProperty(key) && !this.excludeKeys.includes(key)) {
             const value = obj[key];
              // Only include actual primitives or null directly under this object
             if (typeof value !== 'object' && !Array.isArray(value) || value === null) {
               primitives[key] = value;
             }
           }
         }
         return primitives;
       },
      /**
       * Determines table headers by getting keys from the first object in an array.
       * Filters out excluded keys.
       */
      getTableHeaders(array) {
        if (!array || array.length === 0) {
          return [];
        }
        // Get keys from the first object
        const firstItem = array[0];
        if (typeof firstItem !== 'object' || firstItem === null) {
           // Handle arrays of primitives if necessary, currently assumes arrays of objects
           return ['Value']; // Or handle differently
        }
        return Object.keys(firstItem).filter(key => !this.excludeKeys.includes(key));
      },
      /**
       * Gets nested value from an object using a potential dot notation key (basic).
       * Currently just gets direct properties based on headers.
       */
      getNestedValue(item, key) {
        // Simple direct property access for now
        // For deeper nesting like 'cst.subject.name', you'd need a more robust getter
        // Example basic getter:
        // const keys = key.split('.');
        // let value = item;
        // for (const k of keys) {
        //   if (value && typeof value === 'object' && value.hasOwnProperty(k)) {
        //     value = value[k];
        //   } else {
        //     return undefined; // or null or '-'
        //   }
        // }
        // return value;

        const value = item[key];

        // Handle nested objects/arrays in cells
        if (Array.isArray(value)) {
            return `[Array (${value.length} items)]`;
        }
         if (typeof value === 'object' && value !== null) {
            // Attempt to display a representative key, or just indicate it's an object
            // This is a simple heuristic - can be improved with a config prop
            if (value.name) return value.name;
            if (value.title) return value.title;
            if (value.id) return `[Object id:${value.id}]`;
             return '[Object]';
         }

        return value; // Display primitive or null directly
      },
      /**
       * Formats keys into human-readable headers using the keyMap prop.
       */
      formatHeader(key) {
        return this.keyMap[key] || key.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').trim().replace(/\b\w/g, l => l.toUpperCase());
        // This regex converts camelCase or snake_case to Title Case
        // Example: user_role -> User Role, createdAt -> Created At
      },
      /**
       * Formats values for display (handles null, booleans, objects/arrays).
       */
      formatValue(value) {
        if (value === null) {
          return '-'; // Display null as a dash or 'N/A'
        }
        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No'; // Display boolean as Yes/No
        }
        // The getNestedValue method already handles nested objects/arrays
        return value;
      }
    },
  };
  </script>

  <style scoped>
  .data-table-display {
    font-family: sans-serif;
    margin-bottom: 20px;
  }

  .primitive-properties {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #eee;
    border-radius: 5px;
    background-color: #f9f9f9;
  }

  .primitive-properties.nested {
      border: none;
      background-color: transparent;
      padding: 0;
      margin: 0;
      margin-top: 5px; /* Small margin above nested properties */
  }

  .property-row {
    display: flex;
    margin-bottom: 5px;
    line-height: 1.5;
    border-bottom: 1px dashed #ddd;
    padding-bottom: 5px;
  }
  .property-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
  }

  .property-key {
    font-weight: bold;
    min-width: 150px; /* Adjust as needed */
    margin-right: 10px;
    color: #555;
  }

  .property-value {
    flex-grow: 1;
    word-break: break-word; /* Prevent long values from overflowing */
  }

  .array-section, .object-section {
    margin-top: 25px;
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #fff;
  }

  .array-section h4, .object-section h4 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 5px;
  }

  .array-count {
      font-size: 0.9em;
      font-weight: normal;
      color: #777;
  }

  .empty-message {
    font-style: italic;
    color: #999;
    padding: 10px;
    background-color: #f0f0f0;
    border-radius: 4px;
  }

  .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .data-table th,
  .data-table td {
    border: 1px solid #eee;
    padding: 10px 12px;
    text-align: left;
    word-break: break-word; /* Prevent long content from pushing table wide */
  }

  .data-table th {
    background-color: #f2f2f2;
    font-weight: bold;
    color: #333;
  }

  .data-table tbody tr:nth-child(even) {
    background-color: #f9f9f9; /* Zebra stripes */
  }

  .data-table tbody tr:hover {
    background-color: #e9e9e9; /* Hover effect */
  }
  </style>