// Инициализация слайдера Glide.js
document.addEventListener('DOMContentLoaded', () => {
  const glideEl = document.getElementById('glide_1');
  if (!glideEl || typeof Glide === 'undefined') return;

  new Glide('#glide_1', {
    type:        'carousel',
    startAt:     0,
    gap:         0,
    hoverpause:  true,
    autoplay:    4000,
    animationDuration: 600,
    perView:     1,
  }).mount();
});
