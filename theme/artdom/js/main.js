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

  /* ---------- Закреплённая сцена: панели приезжают снизу ----------
     Построение снято с mimcocapital.com. Замеры при экране 900 (V — высота
     сцены, T — сколько секции ещё осталось заехать, d — ход текущего
     перехода):

       въезд:  кадр   = scale(1 + 0.45*T/V) translateY(-0.25*T)
               текст  = translateY(-T)
       смена:  уходящая панель = translateY(-d/4), её текст = translateY(+d/4)
               приходящая      = translateY(V-d),  её текст = translateY(-(V-d))
               кадр приходящей = scale(1 + 0.40*(V-d)/V)

     Всё линейно по прокрутке — никаких кривых и длительностей. Суть в двух
     вещах. Первая: текст смещается НАВСТРЕЧУ ходу своей панели ровно на её
     ход, поэтому на экране он неподвижен и просто открывается снизу вверх.
     Вторая: уходящая панель уезжает на ЧЕТВЕРТИ скорости приходящей — от
     этого у смены появляется глубина, а не эффект сдвинутой бумаги.

     Считаем через getBoundingClientRect, а не наблюдателем: тот отдаёт
     результат асинхронно, а браузер восстанавливает прокрутку ПОСЛЕ
     скриптов — при F5 на середине сцена стояла бы в стартовом положении. */
  Array.prototype.forEach.call(document.querySelectorAll("[data-guaranty]"), function (sec) {
    var stage = sec.querySelector(".guaranty__stage");
    var steps = sec.querySelectorAll(".guaranty__steps span");
    var части = Array.prototype.map.call(sec.querySelectorAll(".guaranty__slide"), function (s) {
      return {
        панель: s,
        кадр: s.querySelector(".guaranty__media"),
        текст: s.querySelector(".guaranty__content")
      };
    });
    var count = части.length;
    if (!count || !stage) return;

    var ВЪЕЗД_МАСШТАБ = 0.45;   /* насколько крупнее приезжает первый кадр */
    var ВЪЕЗД_ЛАГ    = 0.25;    /* и насколько отстаёт от прокрутки */
    var СМЕНА_МАСШТАБ = 0.40;   /* насколько крупнее приезжает следующий кадр */
    var УХОД_ЛАГ      = 0.25;   /* доля скорости, с которой уезжает предыдущий */

    var тихо = window.matchMedia
      && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var ждём = false;

    function поставить(ч, панельY, текстY, масштаб, кадрY) {
      ч.панель.style.transform = "translate3d(0," + панельY.toFixed(2) + "px,0)";
      ч.текст.style.transform = "translate3d(0," + текстY.toFixed(2) + "px,0)";
      /* Порядок важен: сдвиг записан ДО увеличения, поэтому считается в
         координатах родителя и на масштаб не умножается. */
      ч.кадр.style.transform =
        "translate3d(0," + кадрY.toFixed(2) + "px,0) scale(" + масштаб.toFixed(4) + ")";
    }

    function заливка(P) {
      for (var n = 0; n < steps.length; n++) {
        var f = P - n;
        if (f < 0) f = 0;
        if (f > 1) f = 1;
        steps[n].style.setProperty("--fill", f.toFixed(3));
      }
    }

    function draw() {
      ждём = false;
      var V = stage.offsetHeight;
      if (!V) return;

      var верх = sec.getBoundingClientRect().top;

      /* Въезд: секция ещё не дошла до верха экрана, работает только первая
         панель — остальные ждут за нижним краем. */
      if (верх > 0) {
        var T = верх > V ? V : верх;
        for (var n = 0; n < count; n++) {
          if (n === 0) {
            поставить(части[0], 0, -T, 1 + ВЪЕЗД_МАСШТАБ * (T / V), -ВЪЕЗД_ЛАГ * T);
          } else {
            поставить(части[n], V, -V, 1 + СМЕНА_МАСШТАБ, 0);
          }
        }
        заливка(1 - T / V);
        return;
      }

      var проезд = (count - 1) * V;
      var u = -верх;
      if (u > проезд) u = проезд;

      var i = count > 1 ? Math.floor(u / V) : 0;
      if (i > count - 2) i = count - 2;
      if (i < 0) i = 0;
      var d = count > 1 ? u - i * V : 0;
      if (d < 0) d = 0;
      if (d > V) d = V;
      /* Человеку, попросившему убрать анимацию, отдаём чистую смену без
         промежуточных положений. */
      if (тихо) d = d < V / 2 ? 0 : V;

      for (var m = 0; m < count; m++) {
        if (m < i) {
          поставить(части[m], -УХОД_ЛАГ * V, УХОД_ЛАГ * V, 1, 0);
        } else if (m === i) {
          поставить(части[m], -УХОД_ЛАГ * d, УХОД_ЛАГ * d, 1, 0);
        } else if (m === i + 1) {
          поставить(части[m], V - d, -(V - d), 1 + СМЕНА_МАСШТАБ * ((V - d) / V), 0);
        } else {
          поставить(части[m], V, -V, 1 + СМЕНА_МАСШТАБ, 0);
        }
      }
      заливка(1 + u / V);
    }

    /* Склеиваем в кадр: событий прокрутки приходит больше, чем браузер
       успевает нарисовать, а работы здесь на девять узлов. */
    function update() {
      if (ждём) return;
      ждём = true;
      requestAnimationFrame(draw);
    }

    window.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
    window.addEventListener("load", update);
    /* Пока вкладка скрыта, кадры не рисуются и rAF не срабатывает — на
       возврате состояние догоняем принудительно. */
    document.addEventListener("visibilitychange", function () {
      if (!document.hidden) draw();
    });
    draw();
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

  /* ---------- Формы ----------
     Открытие и закрытие держит нативный <dialog>: Esc, перехват фокуса и
     подложка у него свои. Нам остаётся отправка и разбор ответа. */
  var forms = document.querySelectorAll("[data-form]");

  if (forms.length && window.ARTDOM) {

    var openDialog = function (kind) {
      var dlg = document.getElementById("form-" + kind);
      if (!dlg) return;
      if (typeof dlg.showModal === "function") dlg.showModal();
      else dlg.setAttribute("open", "");            /* очень старый браузер: хотя бы покажем */
      var first = dlg.querySelector(".field__input");
      if (first) first.focus();
    };

    document.addEventListener("click", function (e) {
      var opener = e.target.closest("[data-form-open]");
      if (opener) {
        e.preventDefault();
        openDialog(opener.getAttribute("data-form-open"));
        return;
      }
      var closer = e.target.closest("[data-form-close]");
      if (closer) {
        var d = closer.closest("dialog");
        if (d) d.close();
      }
    });

    /* Клик по подложке: у dialog она принадлежит самому элементу,
       поэтому цель события — сам dialog, а не его содержимое. */
    Array.prototype.forEach.call(document.querySelectorAll("dialog.modal"), function (dlg) {
      dlg.addEventListener("click", function (e) {
        if (e.target === dlg) dlg.close();
      });
    });

    var showError = function (field, text) {
      var box = field.closest(".field, .check");
      if (!box) return;
      box.classList.add("is-bad");
      var slot = box.querySelector(".field__error");
      if (slot) slot.textContent = text;
      field.setAttribute("aria-invalid", "true");
    };

    var clearErrors = function (form) {
      Array.prototype.forEach.call(form.querySelectorAll(".is-bad"), function (b) {
        b.classList.remove("is-bad");
        var slot = b.querySelector(".field__error");
        if (slot) slot.textContent = "";
      });
      Array.prototype.forEach.call(form.querySelectorAll("[aria-invalid]"), function (f) {
        f.removeAttribute("aria-invalid");
      });
    };

    /* Проверка на стороне браузера — только чтобы не гонять заведомо пустое.
       Настоящая проверка всё равно на сервере: сюда можно не заходить вовсе. */
    var validate = function (form) {
      var kind = form.getAttribute("data-form");
      var bad = null;

      if (kind === "subscribe") {
        var mail = form.elements.email;
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(mail.value.trim())) {
          showError(mail, "Проверьте адрес почты."); bad = bad || mail;
        }
      } else {
        var nm = form.elements.name;
        if (nm.value.trim().length < 2) { showError(nm, "Как к вам обращаться?"); bad = bad || nm; }
        var ph = form.elements.phone;
        if (ph.value.replace(/\D/g, "").length < 10) { showError(ph, "Проверьте номер телефона."); bad = bad || ph; }
      }

      var consent = form.elements.consent;
      if (!consent.checked) { showError(consent, "Нужно ваше согласие."); bad = bad || consent; }

      return bad;
    };

    Array.prototype.forEach.call(forms, function (form) {
      var note = form.querySelector(".modal__note");
      var submit = form.querySelector(".modal__submit");

      form.addEventListener("submit", function (e) {
        e.preventDefault();                 /* method="dialog" закрыл бы окно молча */
        clearErrors(form);
        if (note) { note.textContent = ""; note.className = "modal__note"; }

        var bad = validate(form);
        if (bad) { bad.focus(); return; }

        var data = new FormData(form);
        data.append("action", "artdom_form");
        data.append("nonce", window.ARTDOM.nonce);
        data.append("page", location.href);

        form.classList.add("is-sending");
        if (submit) submit.disabled = true;

        fetch(window.ARTDOM.ajax, { method: "POST", body: data, credentials: "same-origin" })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (res && res.success) {
              form.classList.add("is-done");
              if (note) { note.className = "modal__note is-ok"; note.textContent = res.data.message; }
              form.reset();
              setTimeout(function () {
                var d = form.closest("dialog");
                if (d) d.close();
                form.classList.remove("is-done");
                if (note) { note.textContent = ""; note.className = "modal__note"; }
              }, 2600);
            } else {
              var msg = (res && res.data && res.data.message) || "Не получилось отправить. Попробуйте ещё раз.";
              var fieldName = res && res.data && res.data.field;
              var target = fieldName && form.elements[fieldName];
              if (target) showError(target, msg);
              else if (note) { note.className = "modal__note is-bad"; note.textContent = msg; }
            }
          })
          .catch(function () {
            if (note) { note.className = "modal__note is-bad"; note.textContent = "Нет связи с сервером. Попробуйте позже."; }
          })
          .then(function () {
            form.classList.remove("is-sending");
            if (submit) submit.disabled = false;
          });
      });

      /* Ошибку убираем, как только человек начал править поле */
      form.addEventListener("input", function (e) {
        var box = e.target.closest(".is-bad");
        if (!box) return;
        box.classList.remove("is-bad");
        var slot = box.querySelector(".field__error");
        if (slot) slot.textContent = "";
        e.target.removeAttribute("aria-invalid");
      });
    });
  }

  /* ---------- Запуск ---------- */
  window.addEventListener("scroll", checkRise, { passive: true });
  window.addEventListener("resize", checkRise);
  window.addEventListener("load", checkRise);
  checkRise();
})();

/* Часы в блоке адреса: показываем московское время независимо от того, где
   находится посетитель. Intl сам знает про переходы и смещения — считать
   разницу руками не нужно и опасно. */
(function () {
  var el = document.querySelector("[data-clock]");
  if (!el) return;

  var fmt;
  try {
    fmt = new Intl.DateTimeFormat("ru-RU", {
      timeZone: "Europe/Moscow",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false
    });
  } catch (e) {
    return;                       /* нет поддержки зоны — лучше прочерк, чем чужое время */
  }

  var tick = function () { el.textContent = fmt.format(new Date()); };
  tick();
  setInterval(tick, 1000);
})();

/* Белая пилюля, которая ездит по меню.
   Одна на всё меню: переезжает под курсор и возвращается на текущий раздел,
   когда указатель уходит. Отдельная заливка у каждого пункта давала бы
   мгновенное переключение — а нужен один непрерывный жест.
   Создаётся скриптом: без него текущий раздел просто залит белым, и меню
   выглядит правильно даже когда скрипт не поднялся. */
(function () {
  var nav = document.querySelector(".nav");
  if (!nav) return;

  var links = Array.prototype.slice.call(nav.querySelectorAll("a"));
  if (!links.length) return;

  var pill = document.createElement("span");
  pill.className = "nav__pill";
  pill.setAttribute("aria-hidden", "true");
  nav.insertBefore(pill, nav.firstChild);
  nav.classList.add("has-pill");

  var active = nav.querySelector(".current-menu-item > a")
    || nav.querySelector(".current_page_parent > a")
    || nav.querySelector(".current-menu-parent > a");

  var поставить = function (el, плавно) {
    links.forEach(function (a) { a.classList.toggle("is-on", a === el); });
    if (!el) { pill.style.opacity = "0"; return; }
    var n = nav.getBoundingClientRect();
    var r = el.getBoundingClientRect();
    if (!плавно) pill.style.transition = "none";
    pill.style.opacity = "1";
    pill.style.width = r.width + "px";
    pill.style.transform = "translateX(" + (r.left - n.left) + "px)";
    if (!плавно) {
      /* Читаем layout, чтобы браузер применил позицию до возврата перехода —
         иначе первая же наводка проедет от нуля через всё меню. */
      void pill.offsetWidth;
      pill.style.transition = "";
    }
  };

  links.forEach(function (a) {
    a.addEventListener("pointerenter", function () { поставить(a, true); });
    a.addEventListener("focus", function () { поставить(a, true); });
  });
  nav.addEventListener("pointerleave", function () { поставить(active, true); });
  nav.addEventListener("focusout", function (e) {
    if (!nav.contains(e.relatedTarget)) поставить(active, true);
  });

  поставить(active, false);
  window.addEventListener("resize", function () { поставить(nav.querySelector("a.is-on") || active, false); });
})();

/* Переключатель цвета шапки по прокрутке снят: цвет теперь выворачивается
   режимом наложения, и два механизма спорили бы друг с другом. */

/* Цвет логотипа и телефона по тому, что сейчас под шапкой.
   Тёмные блоки перечислены классами: первый экран с видео, закреплённая
   сцена «Надёжность» и подвал. Проверяем не пересечение целиком, а одну
   линию — нижний край шапки: именно на ней логотип и лежит.
   Считаем по scroll, а не наблюдателем: тот срабатывает асинхронно, и при
   перезагрузке на середине страницы цвет на миг оставался бы прежним. */
(function () {
  var hdr = document.querySelector(".hdr");
  if (!hdr) return;

  /* Логотип и бургер метим тем же классом. Разностное наложение вычитает подложку из
     белого, поэтому на СРЕДНЕМ тоне оно даёт средний же тон: на фотографии
     первого экрана логотип выходил мутно-серым и терялся. Фотографии и видео
     у нас лежат ровно в этих блоках, значит здесь наложение выключаем и
     красим просто белым — как бургер рядом. На светлых секциях подложка
     плоская и почти белая, вычитание даёт чистый чёрный, и наложение
     остаётся работать само. */
  var выворот = Array.prototype.slice.call(
    document.querySelectorAll(".logo, .hdr__burger")
  );

  var тёмные = Array.prototype.slice.call(
    document.querySelectorAll(".hero, .guaranty, .ftr, .fnext")
  );
  if (!тёмные.length) return;

  var было = null;

  var смотреть = function () {
    var h = hdr.getBoundingClientRect();
    var линия = h.bottom - Math.min(12, h.height / 4);
    var надо = тёмные.some(function (el) {
      var r = el.getBoundingClientRect();
      return r.top <= линия && r.bottom >= линия;
    });
    if (надо !== было) {
      было = надо;
      hdr.classList.toggle("on-dark", надо);
      выворот.forEach(function (el) { el.classList.toggle("on-dark", надо); });
    }
  };

  window.addEventListener("scroll", смотреть, { passive: true });
  window.addEventListener("resize", смотреть);
  window.addEventListener("load", смотреть);
  смотреть();
})();
