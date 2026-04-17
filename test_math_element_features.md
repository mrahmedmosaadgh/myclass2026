# Math Element Test Cases

This document demonstrates the enhanced math element features in Presentation Builder V7.

## New Features Implemented:

### 1. HTML Element Replaced with Math Element
- HTML option removed from Add Element dropdown
- Math element now supports both math and HTML content
- Tooltip updated to indicate Markdown & HTML support

### 2. Enhanced Markdown Support
- Headers: # H1, ## H2, ### H3
- Bold: **text** or __text__
- Italic: *text* or _text_
- Code: `inline code` and ```code blocks```
- Lists: * item, - item, 1. numbered
- Links: [text](url)
- Horizontal rules: --- or ***

### 3. HTML Support with Sanitization
- Safe HTML tags allowed: div, span, p, br, strong, em, u, i, b, code, pre, blockquote, small, sub, sup, mark
- Dangerous tags removed: script, iframe, object, embed, form, input, button, select, textarea, link, meta, style
- Event handlers and javascript: URLs removed for security

### 4. Traditional Math Delimiters
- **Display Math**: $$ formula $$
- **Inline Math**: $ formula $
- LaTeX delimiters also supported: \[...\] and \(...\)

## Test Examples:

### Math with Markdown:
```
# Math Formula Example

Here is an inline formula: $ \frac{a}{b} $ in the middle of text.

And here is a display formula:
$$ \int_0^\infty e^{-x^2} dx = \frac{\sqrt{\pi}}{2} $$

**Important**: This formula shows the Gaussian integral.
```

### Math with HTML:
```
<div style="background: #f0f9ff; padding: 15px; border-radius: 8px;">
  <h3>Physics Formula</h3>
  <p>The famous equation: $$ E = mc^2 $$</p>
  <p>Where <strong>E</strong> is energy, <strong>m</strong> is mass, and <strong>c</strong> is the speed of light.</p>
</div>
```

### Combined Math, Markdown, and HTML:
```
# Advanced Math Example

<div style="border: 2px solid #3b82f6; padding: 10px; border-radius: 4px;">
  <h4>Pythagorean Theorem</h4>
  
  For a right triangle with legs <em>a</em> and <em>b</em>, and hypotenuse <em>c</em>:
  
  $$ a^2 + b^2 = c^2 $$
  
  **Example**: If a = 3 and b = 4, then:
  $$ c = \sqrt{3^2 + 4^2} = \sqrt{25} = 5 $$
</div>

*Key point*: This theorem is fundamental in geometry.
```

## Usage Instructions:

1. **Add Math Element**: Click "Add Element" > "Math"
2. **Edit Content**: Double-click the math element to edit
3. **Use Traditional Delimiters**: 
   - Inline math: `$ \frac{x}{y} $`
   - Display math: `$$ \frac{x}{y} $$`
4. **Mix Content**: Combine math with Markdown and HTML tags
5. **Paste HTML**: Pasting HTML content now creates math elements with full MD/HTML support

## Security Notes:

- All HTML content is sanitized to prevent XSS attacks
- Dangerous tags and attributes are automatically removed
- Event handlers and javascript: URLs are stripped
- Only safe styling attributes are preserved
