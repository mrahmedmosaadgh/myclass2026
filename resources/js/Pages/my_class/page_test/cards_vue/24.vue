<style scoped>
  .card {
    display: grid;
    place-content: center;
    width: 100%;
    height: 100%;
    background: #e7e7e7;
    font-family: "Quicksand", sans-serif;
    -webkit-tap-highlight-color: transparent;
  }

  .clay-slab {
    max-width: 333px;
    padding: 1rem;
    border-radius: 40px;
    background: #e7e7e7;
    box-shadow:
      inset 6px 6px 12px #c5c5c5,
      inset -6px -6px 12px #ffffff;
  }

  .container-items {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
  }

  .item-color {
    position: relative;
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    border: none;
    outline: none;
    cursor: pointer;
    background-color: var(--color);
    border-radius: 41% 59% 45% 55% / 58% 44% 56% 42%;
    transition: all 300ms cubic-bezier(0.165, 0.84, 0.44, 1);
    box-shadow:
      4px 4px 8px rgba(160, 160, 160, 0.6),
      -4px -4px 8px rgba(255, 255, 255, 0.8),
      inset 2px 2px 4px color-mix(in srgb, var(--color) 80%, black),
      inset -2px -2px 4px color-mix(in srgb, var(--color) 80%, white);
  }

  .item-color::before {
    position: absolute;
    content: attr(aria-color);
    left: 50%;
    bottom: 120%;
    transform: translateX(-50%) scale(0);
    padding: 8px 12px;
    background: #e7e7e7;
    border-radius: 20px;
    font-size: 14px;
    color: #555;
    white-space: nowrap;
    box-shadow:
      inset 2px 2px 4px #c5c5c5,
      inset -2px -2px 4px #ffffff;
    pointer-events: none;
    opacity: 0;
    transform-origin: bottom center;
    transition: all 300ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  .item-color:hover {
    transform: translateY(-5px) scale(1.05);
    border-radius: 50%;
  }

  .item-color:hover::before {
    opacity: 1;
    transform: translateX(-50%) scale(1);
  }

  .item-color:active {
    transform: translateY(2px) scale(0.95);
    box-shadow:
      1px 1px 4px rgba(160, 160, 160, 0.6),
      -1px -1px 4px rgba(255, 255, 255, 0.8),
      inset 8px 8px 16px color-mix(in srgb, var(--color) 80%, black),
      inset -8px -8px 16px color-mix(in srgb, var(--color) 80%, white);
  }

  .item-color:focus::before {
    content: "Copied";
    color: #10b981;
    opacity: 1;
    transform: translateX(-50%) scale(1);
  }
</style>

<template>
  <div class="card">
    <div class="clay-slab">
      <div class="container-items">
        <button
          class="item-color"
          style="--color: #e11d48"
          aria-color="#e11d48"
        ></button>
        <button
          class="item-color"
          style="--color: #f472b6"
          aria-color="#f472b6"
        ></button>
        <button
          class="item-color"
          style="--color: #fb923c"
          aria-color="#fb923c"
        ></button>
        <button
          class="item-color"
          style="--color: #facc15"
          aria-color="#facc15"
        ></button>
        <button
          class="item-color"
          style="--color: #84cc16"
          aria-color="#84cc16"
        ></button>
        <button
          class="item-color"
          style="--color: #10b981"
          aria-color="#10b981"
        ></button>
        <button
          class="item-color"
          style="--color: #0ea5e9"
          aria-color="#0ea5e9"
        ></button>
        <button
          class="item-color"
          style="--color: #3b82f6"
          aria-color="#3b82f6"
        ></button>
        <button
          class="item-color"
          style="--color: #8b5cf6"
          aria-color="#8b5cf6"
        ></button>
        <button
          class="item-color"
          style="--color: #a78bfa"
          aria-color="#a78bfa"
        ></button>
      </div>
    </div>
  </div>
</template>
