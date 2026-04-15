// ── AGE GATE ──────────────────────────────
(function () {
  const gate = document.getElementById('ageGate');
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

// ── HAMBURGER MENU ────────────────────────
document.getElementById('hamburger').addEventListener('click', function () {
  document.getElementById('mobileMenu').classList.toggle('open');
});

// ── FILTER TAGS ───────────────────────────
document.querySelectorAll('.filter-tags .tag').forEach(function (btn) {
  btn.addEventListener('click', function () {
    // active state
    document.querySelectorAll('.filter-tags .tag').forEach(function (b) {
      b.classList.remove('active');
    });
    this.classList.add('active');

    var filter = this.dataset.filter;
    filterByTag(filter);
  });
});

function filterByTag(city) {
  document.querySelectorAll('.profile-card').forEach(function (card) {
    if (!city || city === 'all') {
      card.classList.remove('hidden');
    } else if (city === 'vip') {
      card.classList.toggle('hidden', !card.querySelector('.vip-badge'));
    } else if (city === 'new') {
      card.classList.toggle('hidden', !card.querySelector('.new-badge'));
    } else {
      card.classList.toggle('hidden', card.dataset.city !== city);
    }
  });
}

// ── HERO SEARCH ───────────────────────────
function filterProfiles() {
  var city = document.getElementById('searchCity').value.toLowerCase();
  var cat  = document.getElementById('searchCat').value.toLowerCase();
  var age  = document.getElementById('searchAge').value;

  document.querySelectorAll('.profile-card').forEach(function (card) {
    var matchCity = !city || card.dataset.city === city;
    var matchAge  = true;
    if (age) {
      var cardAge = parseInt(card.dataset.age, 10);
      if (age === '18 – 21') matchAge = cardAge >= 18 && cardAge <= 21;
      else if (age === '22 – 29') matchAge = cardAge >= 22 && cardAge <= 29;
      else if (age === '30 – 39') matchAge = cardAge >= 30 && cardAge <= 39;
      else if (age === '40+') matchAge = cardAge >= 40;
    }
    card.classList.toggle('hidden', !(matchCity && matchAge));
  });

  // scroll to grid
  document.querySelector('.profile-section').scrollIntoView({ behavior: 'smooth' });
}

// ── SORT ─────────────────────────────────
function sortProfiles() {
  var val  = document.getElementById('sortSelect').value;
  var grid = document.getElementById('profileGrid');
  var cards = Array.from(grid.querySelectorAll('.profile-card'));

  cards.sort(function (a, b) {
    if (val === 'az') {
      return a.dataset.name.localeCompare(b.dataset.name);
    } else if (val === 'age_asc') {
      return parseInt(a.dataset.age) - parseInt(b.dataset.age);
    }
    return 0; // newest – keep original order
  });

  cards.forEach(function (c) { grid.appendChild(c); });
}

// ── LOAD MORE (placeholder) ───────────────
document.querySelector('.btn-load-more').addEventListener('click', function () {
  this.textContent = 'Wird geladen…';
  var self = this;
  setTimeout(function () {
    self.textContent = 'Keine weiteren Profile';
    self.disabled = true;
    self.style.opacity = '0.5';
  }, 800);
});

// ── STICKY NAVBAR SHADOW ──────────────────
window.addEventListener('scroll', function () {
  var nav = document.querySelector('.navbar');
  if (window.scrollY > 10) {
    nav.style.boxShadow = '0 4px 24px rgba(0,0,0,.5)';
  } else {
    nav.style.boxShadow = 'none';
  }
});
