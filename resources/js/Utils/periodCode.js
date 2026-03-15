/**
 * Period Code Generator - Frontend Utility
 * 
 * Generates standardized period codes matching backend format:
 * Y{year_id}-S{semester}-W{iso_week}-D{day}-P{period}
 * Example: Y2026-S1-W12-D2-P3
 * 
 * Uses ISO week calculation to match backend PeriodCodeGenerator
 */

import { getISOWeek } from 'date-fns';

/**
 * Generate a period code from components
 * @param {number} yearId - Academic year ID
 * @param {number} semester - Semester number (1 or 2)
 * @param {string|Date} date - Date of the session
 * @param {number} dayNumber - Day number (1-7, Sunday-Saturday)
 * @param {number} periodNumber - Period number in the day
 * @returns {string} Formatted period code
 */
export function generatePeriodCode(yearId, semester, date, dayNumber, periodNumber) {
  const jsDate = typeof date === 'string' ? new Date(date) : date;
  const isoWeek = getISOWeek(jsDate);
  
  return `Y${yearId}-S${semester}-W${String(isoWeek).padStart(2, '0')}-D${dayNumber}-P${periodNumber}`;
}

/**
 * Parse a period code into its components
 * @param {string} periodCode - Period code to parse
 * @returns {Object} Parsed components
 * @throws {Error} If invalid format
 */
export function parsePeriodCode(periodCode) {
  // Pattern: Y2026-S1-W12-D2-P3
  const regex = /^Y(\d+)-S(\d+)-W(\d+)-D(\d+)-P(\d+)$/;
  const matches = periodCode.match(regex);
  
  if (!matches || matches.length !== 6) {
    throw new Error(`Invalid period code format: ${periodCode}`);
  }
  
  return {
    year_id: parseInt(matches[1], 10),
    semester: parseInt(matches[2], 10),
    iso_week: parseInt(matches[3], 10),
    day_number: parseInt(matches[4], 10),
    period_number: parseInt(matches[5], 10),
  };
}

/**
 * Validate a period code format
 * @param {string} periodCode - Period code to validate
 * @returns {boolean} True if valid
 */
export function isValidPeriodCode(periodCode) {
  const regex = /^Y(\d+)-S(\d+)-W(\d+)-D(\d+)-P(\d+)$/;
  return regex.test(periodCode);
}

/**
 * Get current academic context for period code generation
 * @param {Object} page - Inertia page props
 * @returns {Object} Current year_id and semester
 */
export function getCurrentAcademicContext(page) {
  const currentYear = page?.props?.currentAcademicYear;
  const currentSemester = page?.props?.currentSemester;
  
  return {
    year_id: currentYear?.id || 1,
    semester: currentSemester?.semester_number || 1,
  };
}
