import { ref, computed } from 'vue'

export interface ImportValidationError {
  field: string
  message: string
}

/**
 * Composable for client-side import file validation
 */
export function useImportValidation() {
  const errors = ref<ImportValidationError[]>([])

  // Allowed file types
  const ALLOWED_EXTENSIONS = ['csv', 'txt', 'xlsx', 'xls']
  const ALLOWED_MIME_TYPES = [
    'text/csv',
    'text/plain',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  ]

  // Maximum file size (10MB in bytes)
  const MAX_FILE_SIZE = 10 * 1024 * 1024

  /**
   * Get file extension from filename
   */
  const getFileExtension = (filename: string): string => {
    const parts = filename.split('.')
    return parts.length > 1 ? parts[parts.length - 1].toLowerCase() : ''
  }

  /**
   * Validate file type
   */
  const validateFileType = (file: File): string | null => {
    const extension = getFileExtension(file.name)
    
    if (!ALLOWED_EXTENSIONS.includes(extension)) {
      return `Invalid file type. Allowed types: ${ALLOWED_EXTENSIONS.join(', ')}`
    }

    // Also check MIME type if available
    if (file.type && !ALLOWED_MIME_TYPES.includes(file.type)) {
      // Some browsers may not set the correct MIME type, so we'll be lenient
      console.warn('File MIME type mismatch:', file.type)
    }

    return null
  }

  /**
   * Validate file size
   */
  const validateFileSize = (file: File): string | null => {
    if (file.size > MAX_FILE_SIZE) {
      const maxSizeMB = MAX_FILE_SIZE / (1024 * 1024)
      const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2)
      return `File size (${fileSizeMB}MB) exceeds maximum allowed size (${maxSizeMB}MB)`
    }

    if (file.size === 0) {
      return 'File is empty'
    }

    return null
  }

  /**
   * Validate file name
   */
  const validateFileName = (file: File): string | null => {
    if (!file.name || file.name.trim().length === 0) {
      return 'File name is invalid'
    }

    // Check for potentially dangerous characters
    const dangerousChars = /[<>:"|?*\x00-\x1f]/
    if (dangerousChars.test(file.name)) {
      return 'File name contains invalid characters'
    }

    return null
  }

  /**
   * Validate import file
   */
  const validateFile = (file: File | null): boolean => {
    errors.value = []

    if (!file) {
      errors.value.push({
        field: 'file',
        message: 'Please select a file to import',
      })
      return false
    }

    // Validate file name
    const fileNameError = validateFileName(file)
    if (fileNameError) {
      errors.value.push({ field: 'file', message: fileNameError })
    }

    // Validate file type
    const fileTypeError = validateFileType(file)
    if (fileTypeError) {
      errors.value.push({ field: 'file', message: fileTypeError })
    }

    // Validate file size
    const fileSizeError = validateFileSize(file)
    if (fileSizeError) {
      errors.value.push({ field: 'file', message: fileSizeError })
    }

    return errors.value.length === 0
  }

  /**
   * Get error message for a specific field
   */
  const getFieldError = (fieldName: string): string | null => {
    const error = errors.value.find(e => e.field === fieldName)
    return error ? error.message : null
  }

  /**
   * Check if a field has an error
   */
  const hasFieldError = (fieldName: string): boolean => {
    return errors.value.some(e => e.field === fieldName)
  }

  /**
   * Clear all errors
   */
  const clearErrors = () => {
    errors.value = []
  }

  /**
   * Clear error for a specific field
   */
  const clearFieldError = (fieldName: string) => {
    errors.value = errors.value.filter(e => e.field !== fieldName)
  }

  /**
   * Set errors from server response
   */
  const setServerErrors = (serverErrors: Record<string, string[]>) => {
    errors.value = []
    Object.entries(serverErrors).forEach(([field, messages]) => {
      messages.forEach(message => {
        errors.value.push({ field, message })
      })
    })
  }

  /**
   * Format file size for display
   */
  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes'

    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
  }

  /**
   * Get file info for display
   */
  const getFileInfo = (file: File) => {
    return {
      name: file.name,
      size: formatFileSize(file.size),
      type: file.type || 'Unknown',
      extension: getFileExtension(file.name),
      lastModified: new Date(file.lastModified).toLocaleString(),
    }
  }

  return {
    errors: computed(() => errors.value),
    validateFile,
    validateFileType,
    validateFileSize,
    validateFileName,
    getFieldError,
    hasFieldError,
    clearErrors,
    clearFieldError,
    setServerErrors,
    formatFileSize,
    getFileInfo,
    ALLOWED_EXTENSIONS,
    MAX_FILE_SIZE,
  }
}
