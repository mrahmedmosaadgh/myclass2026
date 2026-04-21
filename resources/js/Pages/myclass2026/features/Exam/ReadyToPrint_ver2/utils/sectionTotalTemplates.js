export function renderSectionTotalHTML(total, options = {}) {
  const value = Number(total)
  const safeTotal = Number.isFinite(value) ? value : 0

  const template = options?.template || 'text'
  const prefix = options?.prefix ?? 'Total:'
  const suffix = options?.suffix ?? 'marks'

  const placement = options?.placement || 'normal'
  const offsetX = Number(options?.offsetXPt)
  const offsetY = Number(options?.offsetYPt)
  const offsetXPt = Number.isFinite(offsetX) ? offsetX : 0
  const offsetYPt = Number.isFinite(offsetY) ? offsetY : 0

  const wrapperStart = (() => {
    if (placement === 'fly_top_right') {
      return (
        '<div class="section-total-fly" style="top:' + offsetYPt + 'pt; right:' + offsetXPt + 'pt;">'
      )
    }
    return ''
  })()

  const wrapperEnd = placement === 'fly_top_right' ? '</div>' : ''

  const boxTopHeightRaw = Number(options?.boxTopHeightPt)
  const boxTopHeightPt = Number.isFinite(boxTopHeightRaw) ? boxTopHeightRaw : 22

  if (template === 'box') {
    const inner = (
      '<div class="section-total-box" aria-label="Section total">' +
      '<div class="section-total-box-top" style="height:' + boxTopHeightPt + 'pt;"></div>' +
      '<div class="section-total-box-value">' + safeTotal + '</div>' +
      '</div>'
    )
    return wrapperStart + inner + wrapperEnd
  }

  const inner = '<div class="section-total-text">' + prefix + ' ' + safeTotal + ' ' + suffix + '</div>'
  return wrapperStart + inner + wrapperEnd
}
