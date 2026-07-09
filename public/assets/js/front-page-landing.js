// --- Dynamic thumbnail fallback (no external service dependency) ---
window.handleThumbFallback = function (imgEl, title, colorHex) {
  imgEl.onerror = null;
  var c = document.createElement('canvas');
  c.width = 400; c.height = 220;
  var ctx = c.getContext('2d');
  ctx.fillStyle = '#' + (colorHex || '1a1a2e');
  ctx.fillRect(0, 0, 400, 220);
  ctx.fillStyle = '#696cff';
  ctx.font = 'bold 18px sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText((title || '3D Model').substring(0, 24), 200, 110);
  imgEl.src = c.toDataURL('image/png');
};

(() => {
  let e = document.querySelector(".layout-navbar"),
    t = document.getElementById("hero-animation"),
    r = document.querySelectorAll(".hero-dashboard-img"),
    o = document.querySelectorAll(".hero-elements-img"),
    n = document.getElementById("swiper-clients-logos"),
    i = document.getElementById("swiper-reviews"),
    a = document.getElementById("reviews-previous-btn"),
    s = document.getElementById("reviews-next-btn"),
    c = document.querySelector(".swiper-button-prev"),
    l = document.querySelector(".swiper-button-next"),
    d = document.querySelector(".price-duration-toggler"),
    u = [].slice.call(document.querySelectorAll(".price-monthly")),
    m = [].slice.call(document.querySelectorAll(".price-yearly"));

  // Hero animation mouse move effects
  if ("1200" <= screen.width && t) {
    t.addEventListener("mousemove", function (n) {
      o.forEach((e) => {
        e.style.transform = "translateZ(1rem)";
      });
      r.forEach((e) => {
        var t = (window.innerWidth - 2 * n.pageX) / 100;
        e.style.transform = `perspective(1200px) rotateX(${(window.innerHeight - 2 * n.pageY) / 100
          }deg) rotateY(${t}deg) scale3d(1, 1, 1)`;
      });
    });

    e.addEventListener("mousemove", function (n) {
      o.forEach((e) => {
        e.style.transform = "translateZ(1rem)";
      });
      r.forEach((e) => {
        var t = (window.innerWidth - 2 * n.pageX) / 100;
        e.style.transform = `perspective(1200px) rotateX(${(window.innerHeight - 2 * n.pageY) / 100
          }deg) rotateY(${t}deg) scale3d(1, 1, 1)`;
      });
    });

    t.addEventListener("mouseout", function () {
      o.forEach((e) => {
        e.style.transform = "translateZ(0)";
      });
      r.forEach((e) => {
        e.style.transform = "perspective(1200px) scale(1) rotateX(0) rotateY(0)";
      });
    });
  }

  // Initialize Reviews Swiper - ensure it runs after Swiper library is loaded
  let reviewsSwiper = null;

  function initReviewsSwiper() {
    // Check if Swiper is available
    if (typeof Swiper === 'undefined') {
      console.error('Swiper library not loaded');
      return;
    }

    const swiperEl = document.getElementById("swiper-reviews");
    if (!swiperEl) {
      setTimeout(initReviewsSwiper, 200);
      return;
    }

    if (reviewsSwiper) return;

    {
      // v165: 4-slide carousel: Use native Swiper loop and autoplay for smooth, unidirectional right-side rotation
      reviewsSwiper = new Swiper(swiperEl, {
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 5,
        grabCursor: true,
        speed: 800,
        allowTouchMove: true,
        watchSlidesProgress: true,
        loop: true, // Enable native loop for infinite right-side rotation
        autoplay: {
          delay: 3500,
          disableOnInteraction: false,
        },
        breakpoints: {
          1200: {
            slidesPerView: 3,
            slidesPerGroup: 1,
            spaceBetween: 26,
          },
          992: {
            slidesPerView: 2,
            slidesPerGroup: 1,
            spaceBetween: 20,
          },
        },
      });

      window.addEventListener("resize", function () {
        if (reviewsSwiper) reviewsSwiper.update();
      });

      setTimeout(function () {
        if (reviewsSwiper) {
          reviewsSwiper.update();
          reviewsSwiper.updateSlidesClasses();
        }
      }, 300);
    }
  }

  // Wait for DOM and Swiper library to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      // Wait for Swiper library to load
      const checkSwiper = setInterval(() => {
        if (typeof Swiper !== 'undefined') {
          clearInterval(checkSwiper);
          initReviewsSwiper();
        }
      }, 50);
      // Timeout after 5 seconds
      setTimeout(() => {
        clearInterval(checkSwiper);
        if (typeof Swiper !== 'undefined') {
          initReviewsSwiper();
        }
      }, 5000);
    });
  } else {
    // DOM is ready, check for Swiper library
    if (typeof Swiper !== 'undefined') {
      setTimeout(initReviewsSwiper, 100);
    } else {
      const checkSwiper = setInterval(() => {
        if (typeof Swiper !== 'undefined') {
          clearInterval(checkSwiper);
          initReviewsSwiper();
        }
      }, 50);
      setTimeout(() => {
        clearInterval(checkSwiper);
      }, 5000);
    }
  }

  // Initialize Clients Logos Swiper
  if (n) {
    new Swiper(n, {
      slidesPerView: 2,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      breakpoints: {
        992: {
          slidesPerView: 5,
        },
        768: {
          slidesPerView: 3,
        },
      },
    });
  }

  // Price duration toggler
  document.addEventListener("DOMContentLoaded", function (e) {
    function t() {
      if (d && d.checked) {
        m.map(function (e) {
          e.classList.remove("d-none");
        });
        u.map(function (e) {
          e.classList.add("d-none");
        });
      } else if (d) {
        m.map(function (e) {
          e.classList.add("d-none");
        });
        u.map(function (e) {
          e.classList.remove("d-none");
        });
      }
    }
    if (d) {
      t();
      d.onchange = function () {
        t();
      };
    }
  });
})();
