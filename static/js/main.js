/* АРТДОМ — поведение лендинга. Без зависимостей.
   Намеренно без IntersectionObserver: он отдаёт результат только при отрисовке кадра,
   а браузер восстанавливает прокрутку ПОСЛЕ выполнения скриптов — при F5 на середине
   страницы или возврате из истории видимая часть осталась бы пустой. */
(function () {
  "use strict";

  var root = document.documentElement;
  root.dataset.riseReady = "1";

  /* ---------- Появление блоков ---------- */
  var rise = Array.prototype.slice.call(document.querySelectorAll("[data-rise]"));
  var still = rise.slice();

  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  function checkRise() {
    if (!still.length) return;
    var limit = window.innerHeight * 0.88;
    var rest = [];
    for (var i = 0; i < still.length; i++) {
      var el = still[i];
      if (el.getBoundingClientRect().top < limit) {
        el.classList.add("is-in");
        runCounters(el);
      } else rest.push(el);
    }
    still = rest;
  }

  /* ---------- Цифры считаются от нуля при появлении ---------- */
  function runCounters(scope) {
    var nums = scope.querySelectorAll(".stats__num");
    for (var i = 0; i < nums.length; i++) countUp(nums[i]);
  }

  function countUp(el) {
    if (el.dataset.counted) return;
    el.dataset.counted = "1";
    /* "14+", "120+", "38", "<1" — приставку и хвост сохраняем как есть */
    var parts = /^(\D*)(\d+)(\D*)$/.exec(el.textContent.trim());
    if (!parts || reduce) return;

    var pre = parts[1], target = parseInt(parts[2], 10), post = parts[3];
    var started = null, dur = 1100;

    el.style.minWidth = el.getBoundingClientRect().width + "px";  /* чтобы соседи не дёргались */

    /* Обнуляем текст ВНУТРИ первого кадра, а не до него.
       Если кадров нет (вкладка в фоне, свёрнутая панель), человек увидит
       настоящее число без анимации, а не залипший ноль. */
    requestAnimationFrame(function step(now) {
      if (started === null) started = now;
      var p = Math.min(1, (now - started) / dur);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = pre + Math.round(target * eased) + post;
      if (p < 1) requestAnimationFrame(step);
      else el.style.minWidth = "";
    });
  }

  /* ---------- Аккордеон ---------- */
  Array.prototype.forEach.call(document.querySelectorAll("[data-acc]"), function (acc) {
    var items = Array.prototype.slice.call(acc.querySelectorAll(".acc__item"));

    acc.addEventListener("click", function (e) {
      var btn = e.target.closest(".acc__btn");
      if (!btn || !acc.contains(btn)) return;

      var item = btn.closest(".acc__item");
      var willOpen = item.dataset.open !== "true";

      items.forEach(function (it) {
        var open = it === item && willOpen;
        it.dataset.open = open ? "true" : "false";
        it.querySelector(".acc__btn").setAttribute("aria-expanded", open ? "true" : "false");
      });
    });
  });

  /* ---------- Слайдеры ---------- */
  Array.prototype.forEach.call(document.querySelectorAll("[data-slider]"), function (slider) {
    if (fine) slider.classList.add("has-js");
    var track = slider.querySelector(".slider__track");
    var thumb = slider.querySelector("[data-thumb]");
    var bar = thumb ? thumb.parentNode : null;

    function paint() {
      if (!thumb || !bar) return;
      var span = track.scrollWidth - track.clientWidth;
      var ratio = track.scrollWidth > 0 ? track.clientWidth / track.scrollWidth : 1;
      if (ratio > 1) ratio = 1;
      var progress = span > 0 ? track.scrollLeft / span : 0;
      var w = bar.clientWidth;
      /* translate внутри одного transform не умножается на соседний scale —
         сдвиг остаётся в координатах родителя, что нам и нужно */
      thumb.style.transform = "translateX(" + (progress * w * (1 - ratio)) + "px) scaleX(" + ratio + ")";
    }

    track.addEventListener("scroll", paint, { passive: true });

    /* Шаг ленты = ширина карточки вместе с зазором. Считаем по факту,
       а не по токенам: на разных ширинах карточек в кадре разное число. */
    function step() {
      var a = track.children[0], b = track.children[1];
      if (!a) return track.clientWidth;
      if (!b) return a.getBoundingClientRect().width;
      return b.getBoundingClientRect().left - a.getBoundingClientRect().left;
    }

    /* Доводка до карточки своей анимацией.
       Родная scroll-snap на всё время доводки выключена классом is-drag —
       иначе она тянет к своей цели одновременно с нами, и получается дёрганье. */
    var animId = null;

    function glide(to, dur) {
      cancelAnimationFrame(animId);
      var max = track.scrollWidth - track.clientWidth;
      to = Math.max(0, Math.min(max, to));
      if (reduce) { track.scrollLeft = to; slider.classList.remove("is-drag"); return; }

      var from = track.scrollLeft, delta = to - from, t0 = null;
      if (Math.abs(delta) < 1) { slider.classList.remove("is-drag"); return; }

      slider.classList.add("is-drag");
      animId = requestAnimationFrame(function run(now) {
        if (t0 === null) t0 = now;
        var p = Math.min(1, (now - t0) / dur);
        var e = 1 - Math.pow(1 - p, 3);        /* длинный хвост, как у нашей кривой */
        track.scrollLeft = from + delta * e;
        if (p < 1) animId = requestAnimationFrame(run);
        else slider.classList.remove("is-drag");
      });
    }

    function settle(velocity) {
      if (!fine) return;
      var s = step();
      if (!s) return;
      /* учитываем скорость броска: сильный рывок должен перекинуть на карточку дальше */
      var projected = track.scrollLeft + velocity * 90;
      glide(Math.round(projected / s) * s, 780);
    }

    /* Перетаскивание мышью.
       setPointerCapture НЕ ставим: с захватом click адресуется треку, а не ссылке
       под курсором, и карточки перестают открываться. Слушаем документ. */
    var down = null;
    var moved = false;
    var lastX = 0, lastT = 0, vel = 0;

    track.addEventListener("pointerdown", function (e) {
      if (e.pointerType === "touch" || e.button !== 0) return;   /* тач листает сам */
      cancelAnimationFrame(animId);
      down = { x: e.clientX, left: track.scrollLeft };
      lastX = e.clientX; lastT = e.timeStamp; vel = 0;
      moved = false;
    });

    document.addEventListener("pointermove", function (e) {
      if (!down) return;
      var dx = e.clientX - down.x;
      if (!moved && Math.abs(dx) < 4) return;                    /* до порога трек не трогаем */
      if (!moved) { moved = true; slider.classList.add("is-drag"); }

      var dt = e.timeStamp - lastT;
      if (dt > 0) {
        /* сглаживаем скорость, иначе последний кадр решает всё и бросок выходит рваным */
        var v = (lastX - e.clientX) / dt;
        vel = vel * 0.7 + v * 0.3;
        lastX = e.clientX; lastT = e.timeStamp;
      }

      track.scrollLeft = down.left - dx;
      e.preventDefault();
    });

    document.addEventListener("pointerup", function (e) {
      if (!down) return;
      var wasMoved = moved;
      down = null;
      if (wasMoved) {
        if (e.timeStamp - lastT > 90) vel = 0;                   /* палец постоял — броска не было */
        settle(vel * 1000);
        setTimeout(function () { moved = false; }, 0);
      } else {
        slider.classList.remove("is-drag");
      }
    });

    /* Гасим клик, если это было перетаскивание. В фазе перехвата — успеть до ссылки. */
    track.addEventListener("click", function (e) {
      if (moved) { e.preventDefault(); e.stopPropagation(); }
    }, true);

    /* Колесо и клавиатура доводятся тем же способом, когда движение затихло */
    var idle = null;
    track.addEventListener("scroll", function () {
      if (!fine || down || slider.classList.contains("is-drag")) return;
      clearTimeout(idle);
      idle = setTimeout(function () { settle(0); }, 130);
    }, { passive: true });

    window.addEventListener("resize", paint);
    paint();
  });

  /* ---------- Видео в первом экране ----------
     autoplay стоит в разметке, чтобы ролик шёл и без скриптов.
     Здесь только гасим его тем, кто просил убрать анимацию: остаётся постер. */
  if (reduce) {
    var heroVideo = document.querySelector(".hero__video");
    if (heroVideo) {
      heroVideo.removeAttribute("autoplay");
      heroVideo.pause();
    }
  }

  /* ---------- Круглый курсор на фотографиях объектов ---------- */
  var cursorEl = document.querySelector("[data-cursor-el]");

  if (cursorEl && fine && !reduce) {
    root.classList.add("has-cursor");
    var targets = document.querySelectorAll("[data-cursor]");
    var pending = null, cx = 0, cy = 0;

    var place = function () {
      pending = null;
      /* именно свойство translate, а не transform: свойства применяются в порядке
         translate -> rotate -> scale -> transform, поэтому смещение внутри transform
         умножалось бы на scale и при затухании стягивало бы кружок к углу экрана */
      cursorEl.style.translate = cx + "px " + cy + "px";
    };

    var follow = function (e) {
      cx = e.clientX; cy = e.clientY;
      if (pending === null) pending = requestAnimationFrame(place);
    };

    var show = function (e) {
      if (e.pointerType && e.pointerType !== "mouse") return;
      cx = e.clientX; cy = e.clientY;
      place();
      cursorEl.dataset.on = "true";
    };

    var hide = function () { cursorEl.dataset.on = "false"; };

    for (var ci = 0; ci < targets.length; ci++) {
      targets[ci].addEventListener("pointerenter", show);
      targets[ci].addEventListener("pointermove", follow);
      targets[ci].addEventListener("pointerleave", hide);
    }
    /* уехали колесом или страница потеряла фокус — кружок не должен зависнуть */
    window.addEventListener("scroll", hide, { passive: true });
    window.addEventListener("blur", hide);
  }

  /* ---------- Закреплённая сцена: фото и заголовок меняются по прокрутке ----------
     Считаем долю проезда каркаса мимо липкой сцены прямо через getBoundingClientRect.
     IntersectionObserver тут не годится по той же причине, что и в появлении блоков:
     при F5 на середине страницы прокрутка восстанавливается ПОСЛЕ скриптов. */
  Array.prototype.forEach.call(document.querySelectorAll("[data-guaranty]"), function (sec) {
    var stage = sec.querySelector(".guaranty__stage");
    var marks = sec.querySelectorAll("[data-slide]");
    var count = sec.querySelectorAll(".guaranty__bg [data-slide]").length;
    var current = -1;

    function update() {
      var travel = sec.offsetHeight - stage.offsetHeight;
      if (travel <= 0 || !count) return;

      var p = (-sec.getBoundingClientRect().top) / travel;
      if (p < 0) p = 0;
      if (p > 0.9999) p = 0.9999;                 /* иначе на самом низу индекс уедет за последний */

      var i = Math.floor(p * count);
      if (i === current) return;
      current = i;

      for (var n = 0; n < marks.length; n++) {
        if (+marks[n].dataset.slide === i) marks[n].dataset.on = "true";
        else marks[n].removeAttribute("data-on");
      }
    }

    window.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
    update();
  });

  /* ---------- Меню на телефоне ---------- */
  var menu = document.getElementById("menu");
  var opener = document.querySelector("[data-menu-open]");

  function setMenu(open) {
    if (!menu) return;
    menu.dataset.open = open ? "true" : "false";
    /* inert убирает и из обхода табом, и из дерева доступности — одним атрибутом */
    if (open) menu.removeAttribute("inert"); else menu.setAttribute("inert", "");
    if (opener) opener.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.classList.toggle("is-locked", open);
    if (open) { var first = menu.querySelector("a"); if (first) first.focus(); }
    else if (opener) opener.focus();
  }

  if (opener) opener.addEventListener("click", function () { setMenu(true); });
  if (menu) {
    menu.addEventListener("click", function (e) {
      if (e.target.closest("[data-menu-close]") || e.target.closest("a")) setMenu(false);
    });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && menu && menu.dataset.open === "true") setMenu(false);
  });

  /* ---------- Плавный переход по якорям ---------- */
  document.addEventListener("click", function (e) {
    var a = e.target.closest('a[href^="#"]');
    if (!a) return;
    var id = a.getAttribute("href");
    if (id.length < 2) return;
    var target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    target.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
    history.replaceState(null, "", id);
  });

  /* ---------- Запуск ---------- */
  window.addEventListener("scroll", checkRise, { passive: true });
  window.addEventListener("resize", checkRise);
  window.addEventListener("load", checkRise);
  checkRise();
})();
