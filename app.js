// Age gate
(function () {
  var gate = document.getElementById('ageGate');
  if (!sessionStorage.getItem('ageVerified')) {
    gate.classList.remove('hidden');
  } else {
    gate.classList.add('hidden');
  }
})();

function enterSite() {
  sessionStorage.setItem('ageVerified', '1');
  document.getElementById('ageGate').classList.add('hidden');
}

// Hamburger
document.getElementById('hamburger').addEventListener('click', function () {
  document.getElementById('mobileMenu').classList.toggle('open');
});

// Category filter
document.querySelectorAll('.cat-item').forEach(function (btn) {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelectorAll('.cat-item').forEach(function (b) {
      b.classList.remove('active');
    });
    this.classList.add('active');
  });
});

// Load more placeholder
function loadMore() {
  var btn = document.querySelector('.btn-load-more');
  btn.textContent = 'Wird geladen…';
  setTimeout(function () {
    btn.textContent = 'Keine weiteren Profile';
    btn.disabled = true;
    btn.style.opacity = '0.5';
  }, 800);
}
