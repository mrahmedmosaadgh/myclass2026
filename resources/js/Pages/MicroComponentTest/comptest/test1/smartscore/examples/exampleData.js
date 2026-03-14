/**
 * Example data structures for IXL line plot and Smart Score components
 */

// Line plot data matching the IXL example
export const LINE_PLOT_DATA = {
  type: "line-plot",
  title: "Stuffed animals",
  description: "Someone counted how many stuffed animals each student has.",
  data: {
    counts: {
      "0": 2,
      "1": 3,
      "2": 4,
      "3": 3,
      "4": 2
    },
    min: 0,
    max: 4,
    step: 1
  },
  axis: {
    label: "Number of stuffed animals"
  },
  visual: {
    xMarkColor: "#FF8C00",
    xMarkSize: "medium",
    animation: true,
    showTicks: true,
    showLabels: true,
    gridVisible: true,
    tickColor: "#666",
    tickHeight: 12,
    tickWidth: 2,
    axisHeight: 24,
    labelMarginTop: 8,
    axisLabelMarginTop: 16
  }
};

// Smart score data
export const SMART_SCORE_DATA = {
  type: "smart-score",
  currentScore: 85,
  maxScore: 100,
  masteryLevel: "Advanced",
  previousScore: 72,
  improvement: "+13",
  visual: {
    showPercentage: true,
    showMasteryBadge: true,
    showImprovement: true,
    colorScheme: "default"
  }
};

// Complete question data structure
export const COMPLETE_QUESTION_DATA = {
  question: {
    id: "q1",
    type: "line-plot",
    text: "How many students in the class have exactly 3 stuffed animals?",
    correctAnswer: 3,
    points: 10
  },
  linePlot: LINE_PLOT_DATA,
  smartScore: SMART_SCORE_DATA
};