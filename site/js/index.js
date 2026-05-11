// Hamburger меню
const hamburger = document.querySelector('.hamburger');
const navList   = document.querySelector('.nav-list');

if (hamburger && navList) {
  hamburger.addEventListener('click', () => navList.classList.toggle('open'));
  document.addEventListener('click', (e) => {
    if (!navList.contains(e.target) && !hamburger.contains(e.target)) {
      navList.classList.remove('open');
    }
  });
}

// Popup (показываем через 2 секунды, если не был закрыт ранее)
const popup      = document.getElementById('popup');
const closePopup = document.getElementById('closePopup');

if (popup && !sessionStorage.getItem('popupClosed')) {
  setTimeout(() => popup.classList.remove('hide-popup'), 2000);
}
if (closePopup) {
  closePopup.addEventListener('click', () => {
    popup.classList.add('hide-popup');
    sessionStorage.setItem('popupClosed', '1');
  });
}
if (popup) {
  popup.addEventListener('click', (e) => {
    if (e.target === popup) {
      popup.classList.add('hide-popup');
      sessionStorage.setItem('popupClosed', '1');
    }
  });
}
