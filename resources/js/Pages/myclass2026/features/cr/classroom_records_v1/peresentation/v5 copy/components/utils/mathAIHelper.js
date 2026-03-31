// AI Math Helper for Presentation Descriptions
// Provides intelligent assistance for mathematical expressions and formulas

class MathAIHelper {
  constructor() {
    this.commonFormulas = {
      quadratic: 'x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}',
      pythagorean: 'a^2 + b^2 = c^2',
      circle: 'x^2 + y^2 = r^2',
      area_circle: 'A = \\pi r^2',
      circumference: 'C = 2\\pi r',
      derivative_power: '\\frac{d}{dx}(x^n) = nx^{n-1}',
      integral_power: '\\int x^n dx = \\frac{x^{n+1}}{n+1} + C',
      euler: 'e^{i\\pi} + 1 = 0',
      sum: '\\sum_{i=1}^{n} i = \\frac{n(n+1)}{2}',
      limit: '\\lim_{x \\to a} f(x) = L'
    };
    
    this.symbols = {
      alpha: '\\alpha',
      beta: '\\beta',
      gamma: '\\gamma',
      delta: '\\delta',
      epsilon: '\\epsilon',
      theta: '\\theta',
      lambda: '\\lambda',
      mu: '\\mu',
      pi: '\\pi',
      sigma: '\\sigma',
      phi: '\\phi',
      omega: '\\omega',
      infinity: '\\infty',
      partial: '\\partial',
      nabla: '\\nabla',
      pm: '\\pm',
      times: '\\times',
      div: '\\div',
      leq: '\\leq',
      geq: '\\geq',
      neq: '\\neq',
      approx: '\\approx'
    };
  }

  async assistWithMath(input) {
    const trimmed = input.trim();
    
    // Check for common formula requests
    if (this.isFormulaRequest(trimmed)) {
      return this.suggestFormula(trimmed);
    }
    
    // Check for symbol requests
    if (this.isSymbolRequest(trimmed)) {
      return this.suggestSymbol(trimmed);
    }
    
    // Try to fix common LaTeX syntax issues
    if (this.isLatexExpression(trimmed)) {
      return this.fixLatexSyntax(trimmed);
    }
    
    // Provide general math assistance
    return this.provideMathHelp(trimmed);
  }

  isFormulaRequest(input) {
    const keywords = ['quadratic', 'pythagorean', 'circle', 'area', 'circumference', 
                     'derivative', 'integral', 'euler', 'sum', 'limit', 'formula'];
    return keywords.some(keyword => input.toLowerCase().includes(keyword));
  }

  isSymbolRequest(input) {
    const symbolNames = Object.keys(this.symbols);
    return symbolNames.some(symbol => input.toLowerCase().includes(symbol));
  }

  isLatexExpression(input) {
    return input.includes('\\') || input.includes('$') || input.includes('{') || input.includes('}');
  }

  suggestFormula(input) {
    const lowerInput = input.toLowerCase();
    
    for (const [name, formula] of Object.entries(this.commonFormulas)) {
      if (lowerInput.includes(name)) {
        return {
          type: 'formula',
          suggestion: `$$${formula}$$`,
          description: `Common ${name} formula`,
          inline: false
        };
      }
    }
    
    return null;
  }

  suggestSymbol(input) {
    const lowerInput = input.toLowerCase();
    
    for (const [name, latex] of Object.entries(this.symbols)) {
      if (lowerInput.includes(name)) {
        return {
          type: 'symbol',
          suggestion: `$${latex}$`,
          description: `${name} symbol`,
          inline: true
        };
      }
    }
    
    return null;
  }

  fixLatexSyntax(input) {
    let fixed = input;
    
    // Common fixes
    fixed = fixed.replace(/sqrt\(/g, '\\sqrt{');
    fixed = fixed.replace(/\)\)/g, '}');
    fixed = fixed.replace(/frac\(/g, '\\frac{');
    fixed = fixed.replace(/,\s*/g, '}{');
    fixed = fixed.replace(/lim_/g, '\\lim_{');
    fixed = fixed.replace(/sum_/g, '\\sum_{');
    fixed = fixed.replace(/int_/g, '\\int_{');
    
    // Ensure proper math delimiters
    if (!fixed.startsWith('$') && !fixed.startsWith('$$')) {
      fixed = `$${fixed}$`;
    }
    
    return {
      type: 'syntax_fix',
      suggestion: fixed,
      description: 'Fixed LaTeX syntax',
      inline: !fixed.includes('$$')
    };
  }

  provideMathHelp(input) {
    // General math assistance based on context
    const suggestions = [
      {
        trigger: ['equation', 'solve'],
        suggestion: 'Try using standard notation: $ax^2 + bx + c = 0$',
        description: 'Standard quadratic equation format'
      },
      {
        trigger: ['integral', 'integrate'],
        suggestion: 'Use: $\\int f(x) dx$ or $$\\int_a^b f(x) dx$$',
        description: 'Integral notation'
      },
      {
        trigger: ['derivative', 'differentiate'],
        suggestion: 'Use: $\\frac{d}{dx}f(x)$ or $f\'(x)$',
        description: 'Derivative notation'
      },
      {
        trigger: ['matrix', 'matrices'],
        suggestion: 'Use: $\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}$',
        description: 'Matrix notation'
      },
      {
        trigger: ['greek', 'symbol'],
        suggestion: 'Available: $\\alpha, \\beta, \\gamma, \\delta, \\theta, \\lambda, \\mu, \\pi, \\sigma, \\phi, \\omega$',
        description: 'Greek letters'
      }
    ];
    
    for (const { trigger, suggestion, description } of suggestions) {
      if (trigger.some(t => input.toLowerCase().includes(t))) {
        return {
          type: 'help',
          suggestion,
          description,
          inline: true
        };
      }
    }
    
    // Default help
    return {
      type: 'help',
      suggestion: 'Available commands: "quadratic formula", "pythagorean", "circle area", or type LaTeX like $x^2 + y^2 = r^2$',
      description: 'Math assistance guide',
      inline: true
    };
  }

  // Get all available formulas for reference
  getAvailableFormulas() {
    return Object.entries(this.commonFormulas).map(([name, formula]) => ({
      name,
      formula: `$$${formula}$$`,
      description: this.getFormulaDescription(name)
    }));
  }

  // Get all available symbols for reference
  getAvailableSymbols() {
    return Object.entries(this.symbols).map(([name, latex]) => ({
      name,
      latex: `$${latex}$`,
      description: `${name} symbol`
    }));
  }

  getFormulaDescription(name) {
    const descriptions = {
      quadratic: 'Quadratic formula solution',
      pythagorean: 'Pythagorean theorem',
      circle: 'Circle equation',
      area_circle: 'Area of a circle',
      circumference: 'Circumference of a circle',
      derivative_power: 'Power rule for derivatives',
      integral_power: 'Power rule for integrals',
      euler: "Euler's identity",
      sum: 'Sum of first n integers',
      limit: 'Limit notation'
    };
    
    return descriptions[name] || 'Mathematical formula';
  }
}

// Create global instance
window.aiAssistant = new MathAIHelper();

export default MathAIHelper;
