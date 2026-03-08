<style scoped>
  .keyboard {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 16px;
    padding-bottom: 19px;
    border-radius: 16px;
    background-color: #666666;
    background-image: linear-gradient(to bottom, #383838, #1f1f1f);
    box-shadow:
      rgba(0, 0, 0, 0.76) 0px 2px 4px,
      rgba(0, 0, 0, 0.39) 0px 7px 13px -3px,
      rgba(0, 0, 0, 0.247) 0px -3px 0px inset;
    width: 600px;
    user-select: none;
  }

  .row {
    display: flex;
    gap: 3px;
  }

  .key {
    background-color: #1f1e1e;
    border: 1px solid rgb(0, 59, 32);
    border-radius: 6px;
    box-shadow:
      rgba(0, 0, 0, 0.801) 0px 2px 4px,
      rgba(1, 255, 213, 0.288) 0px 7px 13px -3px,
      rgba(9, 255, 202, 0.247) 0px -1px 0px inset;
    min-width: 35px;
    text-align: center;
    padding: 8px 5px;
    font-size: 10px;
    color: #00ffbf;
    cursor: pointer;
    transition:
      box-shadow 0.2s,
      background-color 0.2s,
      transform 0.1s;
  }

  .key:hover {
    background-color: #2e2e2e;
    transform: translateY(-2px);
    box-shadow:
      rgba(0, 0, 0, 0.4) 0px 2px 5px,
      rgba(0, 0, 0, 0.3) 0px 7px 16px -3px,
      rgba(0, 0, 0, 0.2) 0px -1px 0px inset;
  }

  .key:active {
    transform: translateY(1px);
    box-shadow:
      rgba(0, 255, 191, 0.644) 0px 2px 3px,
      rgb(0, 0, 0) 0px 5px 10px -3px,
      rgba(0, 255, 191, 0.973) 0px -1px 0px inset;
  }

  .function-key {
    max-height: 25px;
    padding: 4px;
  }

  .eject-key {
    margin-left: 16px;
    padding: 4px 24px;
  }

  .delete-key {
    padding: 8px 24px;
  }

  .tab-key,
  .backslash-key,
  .caps-lock-key,
  .return-key {
    flex: 2;
  }

  .shift-key {
    flex: 3;
  }

  .space-key {
    flex: 5;
    padding: 8px;
    min-width: 175px;
  }

  .command-key,
  .alt-key {
    min-width: 35px;
    padding: 2px;
    font-size: 16px;
  }

  .arrow-key {
    min-width: 30px;
  }
</style>

<template>
  <div class="keyboard">
    <div class="row">
      <div class="key function-key">esc</div>
      <div class="key function-key">F1</div>
      <div class="key function-key">F2</div>
      <div class="key function-key">F3</div>
      <div class="key function-key">F4</div>
      <div class="key function-key">F5</div>
      <div class="key function-key">F6</div>
      <div class="key function-key">F7</div>
      <div class="key function-key">F8</div>
      <div class="key function-key">F9</div>
      <div class="key function-key">F10</div>
      <div class="key function-key">F11</div>
      <div class="key function-key">F12</div>
      <div class="key eject-key">⏏</div>
    </div>

    <div class="row">
      <div class="key">`</div>
      <div class="key">1</div>
      <div class="key">2</div>
      <div class="key">3</div>
      <div class="key">4</div>
      <div class="key">5</div>
      <div class="key">6</div>
      <div class="key">7</div>
      <div class="key">8</div>
      <div class="key">9</div>
      <div class="key">0</div>
      <div class="key">-</div>
      <div class="key">=</div>
      <div class="key delete-key">delete</div>
    </div>

    <div class="row">
      <div class="key tab-key">tab</div>
      <div class="key">Q</div>
      <div class="key">W</div>
      <div class="key">E</div>
      <div class="key">R</div>
      <div class="key">T</div>
      <div class="key">Y</div>
      <div class="key">U</div>
      <div class="key">I</div>
      <div class="key">O</div>
      <div class="key">P</div>
      <div class="key">[</div>
      <div class="key">]</div>
      <div class="key backslash-key">\</div>
    </div>

    <div class="row">
      <div class="key caps-lock-key">caps lock</div>
      <div class="key">A</div>
      <div class="key">S</div>
      <div class="key">D</div>
      <div class="key">F</div>
      <div class="key">G</div>
      <div class="key">H</div>
      <div class="key">J</div>
      <div class="key">K</div>
      <div class="key">L</div>
      <div class="key">;</div>
      <div class="key">'</div>
      <div class="key return-key">return</div>
    </div>

    <div class="row">
      <div class="key shift-key">shift</div>
      <div class="key">Z</div>
      <div class="key">X</div>
      <div class="key">C</div>
      <div class="key">V</div>
      <div class="key">B</div>
      <div class="key">N</div>
      <div class="key">M</div>
      <div class="key">,</div>
      <div class="key">.</div>
      <div class="key">/</div>
      <div class="key shift-key">shift</div>
    </div>

    <div class="row">
      <div class="key">fn</div>
      <div class="key ctrl-key">ctrl</div>
      <div class="key alt-key">⌥</div>
      <div class="key command-key">⌘</div>
      <div class="key space-key"></div>
      <div class="key command-key">⌘</div>
      <div class="key alt-key">⌥</div>
      <div class="key arrow-key">◀</div>
      <div class="key arrow-key">▼</div>
      <div class="key arrow-key">▲</div>
      <div class="key arrow-key">▶</div>
    </div>
  </div>
</template>
