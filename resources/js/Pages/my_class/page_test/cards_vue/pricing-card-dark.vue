<!-- This file was previously named 7.vue - A dark-themed pricing card -->
<style scoped>
  .main__heading {
    font-weight: 600;
    font-size: 2.25em;
    margin-bottom: 0.75em;
    text-align: center;
    color: #eceff1;
  }

  .cards {
    position: relative;
  }

  .card {
    --flow-space: 0.5em;
    --hsl: var(--hue), var(--saturation), var(--lightness);
    flex: 1 1 14rem;
    padding: 1.5em 2em;
    display: grid;
    grid-template-rows: auto auto auto 1fr;
    align-items: start;
    gap: 1.25em;
    color: #eceff1;
    background-color: #2b2b2b;
    border: 1px solid #eceff133;
    border-radius: 15px;
  }

  .card:nth-child(1) {
    --hue: 165;
    --saturation: 82.26%;
    --lightness: 51.37%;
  }

  .card__bullets {
    line-height: 1.4;
  }

  .card__heading {
    font-size: 1.05em;
    font-weight: 600;
  }

  .card__price {
    font-size: 1.75em;
    font-weight: 700;
  }

  .flow > * + * {
    margin-top: var(--flow-space, 1.25em);
  }
</style>

<template>
  <div class="pricing-container">
    <h2 class="main__heading">Pricing Plans</h2>
    <div class="cards">
      <div class="card">
        <div class="card__header">
          <h3 class="card__heading">Basic Plan</h3>
          <div class="card__price">$9<span>/month</span></div>
        </div>
        <ul class="card__bullets flow">
          <li>Feature one</li>
          <li>Feature two</li>
          <li>Feature three</li>
        </ul>
      </div>
    </div>
  </div>
</template>