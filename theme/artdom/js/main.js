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

    /* Доехали до низа страницы — ниже порога уже ничто не окажется: показываем
       всё оставшееся. Иначе блок в последней сотне пикселей документа (или
       любой блок на странице короче окна) не появился бы никогда. */
    if (rest.length && window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2) {
      for (var j = 0; j < rest.length; j++) { rest[j].classList.add("is-in"); runCounters(rest[j]); }
      rest = [];
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

      /* velocity приходит в пикселях за СЕКУНДУ, а множитель 90 читался как
         «миллисекунды инерции» — бросок улетал на десятки тысяч пикселей и
         ленту кидало сразу в конец. Считаем честно: 0.12 с выбега. */
      var projected = track.scrollLeft + velocity * 0.12;

      /* И ограничиваем броском на два кадра: даже резкий рывок мышью не
         должен пролистывать всю ленту — человек теряет место. */
      var предел = s * 2;
      projected = Math.max(track.scrollLeft - предел, Math.min(track.scrollLeft + предел, projected));

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

    /* Стрелки — часть общего примитива, а не отдельный слайдер под галерею.
       Их может не быть: у лент на главной их нет, и код это переживает. */
    var prev = slider.querySelector("[data-slider-prev]");
    var next = slider.querySelector("[data-slider-next]");

    function края() {
      /* Лента, которая влезла целиком, перестаёт быть лентой: ни бегунка, ни
         стрелок. Проверяем по факту, а не по числу карточек — на узком
         экране три штуки уже не помещаются, а на широком и четыре влезут. */
      var max = track.scrollWidth - track.clientWidth;
      slider.classList.toggle("slider--static", max <= 1);

      if (!prev && !next) return;
      /* Допуск в пиксель: дробные ширины кадра дают scrollLeft вроде 1091.5,
         и без него стрелка «вперёд» гасла бы на предпоследнем снимке. */
      if (prev) prev.disabled = track.scrollLeft <= 1;
      if (next) next.disabled = track.scrollLeft >= max - 1;
    }

    if (prev) prev.addEventListener("click", function () { glide(track.scrollLeft - step(), 420); });
    if (next) next.addEventListener("click", function () { glide(track.scrollLeft + step(), 420); });
    track.addEventListener("scroll", края, { passive: true });
    window.addEventListener("resize", края);
    края();
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
    var живая = false;
    var прогрето = false;

    /* Слои поднимаем на видеокарту только пока сцена рядом с экраном, и там же
       заранее раскодируем фотографии. Ленивая картинка декодируется в тот
       момент, когда её впервые показывают, — а показывают её ровно в середине
       перехода, и декод в 1920x1080 съедает кадров на десяток. Раскодировать
       заранее дешевле: сама загрузка всё равно ленивая, меняется только
       момент разбора. */
    function оживить(надо) {
      if (надо === живая) return;
      живая = надо;
      sec.classList.toggle("is-live", надо);
      if (!надо || прогрето) return;
      прогрето = true;
      Array.prototype.forEach.call(sec.querySelectorAll(".guaranty__media img"), function (img) {
        img.loading = "eager";
        if (img.decode) img.decode().catch(function () {});
      });
    }

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

    /* Высоту сцены держим в переменной и пересчитываем только по resize:
       offsetHeight — это принудительный пересчёт раскладки, а звать его на
       каждый кадр прокрутки незачем, меняется он только вместе с окном. */
    var V = 0;
    function мерить() { V = stage.offsetHeight; }
    мерить();

    function draw() {
      ждём = false;
      if (!V) мерить();
      if (!V) return;

      var короб = sec.getBoundingClientRect();
      var верх = короб.top;

      /* Запас в экран с каждой стороны: слои успевают подняться и картинки
         раскодироваться до того, как сцена понадобится. */
      оживить(короб.bottom > -V && короб.top < window.innerHeight + V);

      /* Въезд: секция ещё не дошла до верха экрана, работает только первая
         панель — остальные ждут за нижним краем. */
      if (верх > 0) {
        var T = верх > V ? V : верх;
        for (var n = 0; n < count; n++) {
          if (n === 0) {
            /* Текст на въезде НЕ трогаем: он просто стоит в своей панели и
               заезжает вместе с ней, как обычное содержимое. Раньше он был
               прибит к экрану (translateY(-T), как между кадрами) — и потому
               вылезал из верхнего края секции и полз по ней, пока она
               заезжала. Смысл встречного смещения только в закреплении, где
               панель едет, а сцена стоит; на въезде едет сама сцена, и
               прибивать текст не к чему. Параллакс фотографии оставлен: он
               незаметен и даёт глубину. */
            поставить(части[0], 0, 0, 1 + ВЪЕЗД_МАСШТАБ * (T / V), -ВЪЕЗД_ЛАГ * T);
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
    window.addEventListener("resize", function () { мерить(); update(); });
    window.addEventListener("load", function () { мерить(); update(); });
    /* Пока вкладка скрыта, кадры не рисуются и rAF не срабатывает — на
       возврате состояние догоняем принудительно. */
    document.addEventListener("visibilitychange", function () {
      if (!document.hidden) draw();
    });
    draw();
  });

  /* ---------- Описания в две строки ----------
     Обрезаем по последнему СЛОВУ, которое умещается, и ставим многоточие
     цветом кнопок — это знак, что текст продолжается и его можно открыть.

     Через CSS так не сделать: -webkit-line-clamp красит многоточие цветом
     текста и рвёт строку посреди слова. Поэтому меряем сами: снимаем
     обрезку, подбираем двоичным поиском число слов, которое влезает в две
     строки, и дописываем хвост.

     Куда ведёт хвост, зависит от того, есть ли куда вести. У статьи есть
     своя страница — там это ссылка. У отзыва страницы нет, поэтому кнопка
     раскрывает текст на месте. */
  var подрезаемые = Array.prototype.slice.call(document.querySelectorAll("[data-clip]"));

  function хвостик(el) {
    var href = el.dataset.clipHref;
    var узел;
    if (href) {
      узел = document.createElement("a");
      узел.href = href;
      /* Заголовок карточки ведёт туда же, поэтому для скринридера этот хвост
         лишний: он прочитал бы «многоточие, ссылка». Прячем от него и из
         обхода табом, мышью он при этом работает. */
      узел.setAttribute("aria-hidden", "true");
      узел.tabIndex = -1;
    } else {
      узел = document.createElement("button");
      узел.type = "button";
      узел.setAttribute("aria-label", "Показать текст полностью");
      узел.addEventListener("click", function () {
        el.textContent = el.dataset.clipFull;
        el.dataset.clipDone = "open";
      });
    }
    узел.className = "clip__more";
    узел.textContent = "…";
    return узел;
  }

  function подрезать(el) {
    if (el.dataset.clipDone === "open") return;   /* раскрыт человеком — не трогаем */

    /* На узком экране карточка занимает всю ширину, и число строк, снятое с
       раскладки в три колонки, режет текст вчетверо сильнее нужного: отзыв
       на 684 знака показывался 79 знаками. Кто хочет другое число на телефоне
       — ставит data-clip-narrow. Порог тот же 860, что у раскладки. */
    var строк = parseInt(el.dataset.clip, 10) || 2;
    if (el.dataset.clipNarrow && window.matchMedia("(max-width: 860px)").matches) {
      строк = parseInt(el.dataset.clipNarrow, 10) || строк;
    }

    if (!el.dataset.clipFull) {
      el.dataset.clipFull = el.textContent.replace(/\s+/g, " ").trim();
    }
    /* Снимаем запасную обрезку из CSS и возвращаем полный текст: мерить
       надо несокращённый, иначе на втором проходе он будет ужиматься. */
    el.dataset.clipOn = "1";
    el.textContent = el.dataset.clipFull;

    var lh = parseFloat(getComputedStyle(el).lineHeight);
    if (!lh) return;
    var предел = lh * строк + 1;
    if (el.scrollHeight <= предел) return;

    var слова = el.dataset.clipFull.split(" ");
    var низ = 1, верх = слова.length, лучш = 1;
    while (низ <= верх) {
      var сер = (низ + верх) >> 1;
      el.textContent = слова.slice(0, сер).join(" ") + "…";
      if (el.scrollHeight <= предел) { лучш = сер; низ = сер + 1; } else { верх = сер - 1; }
    }

    /* Точку или запятую перед многоточием убираем — иначе выходит «бумаге.…».
       И отбрасываем последнее слово, если оно короче трёх букв: обрыв на
       предлоге читается опечаткой, а не сокращением. Заказчик поймал это на
       карточке объекта — там текст кончался одинокой «В…». */
    function поставить(n) {
      var хвост = слова.slice(0, n);
      while (хвост.length > 1 && хвост[хвост.length - 1].replace(/[^\wа-яёА-ЯЁ]/g, "").length <= 2) {
        хвост.pop();
      }
      var т = хвост.join(" ").replace(/[.,;:!?—–-]+$/, "");
      /* БЕЗ пробела перед хвостом: мерили мы «слово…» слитно, а пробел плюс
         отдельный узел шире и иногда переносят строку. На этом ряд
         разъезжался по высоте. */
      el.textContent = т;
      el.appendChild(хвостик(el));
    }

    поставить(лучш);
    /* Проверка ПОСЛЕ вставки: хвост — отдельный узел, и его перенос считается
       иначе, чем у той же строки одним куском. Если не влезли — отступаем
       по слову, пока не влезем. */
    while (el.scrollHeight > предел && лучш > 1) {
      лучш -= 1;
      поставить(лучш);
    }
  }

  function подрезатьВсе() { подрезаемые.forEach(подрезать); }

  if (подрезаемые.length) {
    подрезатьВсе();
    /* Шрифт грузится позже разметки, и с ним меняется перенос строк. */
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(подрезатьВсе); }
    var перещёт = null;
    window.addEventListener("resize", function () {
      clearTimeout(перещёт);
      перещёт = setTimeout(подрезатьВсе, 150);
    });
  }

  /* ---------- Подгрузка отзывов ----------
     Забираем следующую страницу тем же адресом, что открылся бы по ссылке,
     и вынимаем из неё строки. Своей точки на сервере не заводим: страница
     уже умеет отдавать вторую порцию, а без скрипта ссылка просто работает
     как ссылка. */
  var списокОтзывов = document.querySelector("[data-revlist]");
  var ещёОтзывы = document.querySelector("[data-revmore]");

  if (списокОтзывов && ещёОтзывы) {
    ещёОтзывы.addEventListener("click", function (e) {
      var ссылка = e.target.closest("a");
      if (!ссылка) return;
      /* preventDefault ВСЕГДА, ещё до проверки занятости. Раньше выход по
         busy стоял раньше него, и второй клик во время загрузки уходил
         браузеру: человек оказывался на /reviews/page/2/ вместо подгрузки.
         На медленной сети двойное нажатие — обычное дело, а выглядит это
         как «кнопка открыла другую страницу». */
      e.preventDefault();
      if (ещёОтзывы.dataset.busy) return;

      var адрес = ссылка.href;
      var надпись = ссылка.textContent;
      ещёОтзывы.dataset.busy = "1";
      ссылка.textContent = "Загружаем…";

      fetch(адрес, { credentials: "same-origin" })
        .then(function (r) {
          /* Без этой проверки страница 404 разбирается как обычная: отзывов
             в ней нет, кнопки «дальше» тоже — и кнопка молча исчезала бы,
             как будто всё показано. Пусть лучше вернётся надпись. */
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.text();
        })
        .then(function (html) {
          var док = new DOMParser().parseFromString(html, "text/html");
          var новые = док.querySelectorAll("[data-revlist] .revrow");
          Array.prototype.forEach.call(новые, function (n) {
            списокОтзывов.insertBefore(document.importNode(n, true), ещёОтзывы);
          });
          var дальше = док.querySelector("[data-revmore] a");
          if (дальше) {
            ссылка.href = дальше.href;
            ссылка.textContent = надпись;
            delete ещёОтзывы.dataset.busy;
          } else {
            /* Больше нечего показывать — кнопка не должна оставаться
               мёртвой. */
            ещёОтзывы.remove();
          }
        })
        .catch(function () {
          ссылка.textContent = надпись;
          delete ещёОтзывы.dataset.busy;
        });
    });
  }

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
    /* Пока меню открыто, шапку не прячем ни при каком направлении: закрыть
       её будет нечем. Признак висит на <html>, там же где hdr-away. */
    root.classList.toggle("menu-open", open);
    if (open) root.classList.remove("hdr-away");
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

      if (kind === "review") {
        var rn = form.elements.name;
        if (rn.value.trim().length < 2) { showError(rn, "Как вас представить?"); bad = bad || rn; }
        /* Группа радиокнопок приходит как коллекция, а не как одно поле:
           у неё нет .value, пока ничего не выбрано. */
        var stars = form.elements.rating;
        var star = stars && (stars.value || (stars.length ? null : stars.checked));
        if (!star) {
          var первая = form.querySelector(".rate__in");
          if (первая) { showError(первая, "Поставьте оценку."); bad = bad || первая; }
        }
        var rt = form.elements.message;
        if (rt.value.trim().length < 20) { showError(rt, "Расскажите чуть подробнее — хотя бы пару фраз."); bad = bad || rt; }
      } else if (kind === "subscribe") {
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

  /* Логотип и бургер класс НЕ получают: они выворачиваются наложением сами,
     по тому, что реально под ними. Попытка выключать наложение над тёмными
     блоками сломала главный случай — белый заголовок первого экрана
     проезжал под белым логотипом, и белое слилось с белым. Мутность на
     фотографии лечится не отменой приёма, а тенью под шапкой: разность
     мутнеет на СРЕДНЕМ тоне, значит подложку надо увести в тёмное.

     Класс нужен только телефону в шапке: он лежит внутри фиксированного
     слоя, и наложение к нему неприменимо. */
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
      /* Логотип и бургер стоят ВНЕ шапки, поэтому метим их отдельно. */
      Array.prototype.forEach.call(
        document.querySelectorAll(".logo, .hdr__burger"),
        function (el) { el.classList.toggle("on-dark", надо); }
      );
    }
  };

  window.addEventListener("scroll", смотреть, { passive: true });
  window.addEventListener("resize", смотреть);
  window.addEventListener("load", смотреть);
  смотреть();
})();

/* Оглавление на странице услуг: подсвечиваем пункт, чей блок сейчас на
   экране. Точка и цвет — как у референса symbolstudio.
   Считаем по прокрутке, а не наблюдателем: блоки высокие, наблюдатель на
   длинном блоке молчит, и подсветка отставала бы на полстраницы. */
(function () {
  "use strict";

  var links = Array.prototype.slice.call(document.querySelectorAll("[data-svcnav]"));
  if (!links.length) return;

  var rows = links
    .map(function (a) { return document.getElementById(a.getAttribute("data-svcnav")); })
    .filter(Boolean);
  if (rows.length !== links.length) return;

  var текущий = -1;

  function смотреть() {
    /* Текущим считаем последний блок, чей верх уже прошёл треть экрана. */
    var порог = window.innerHeight * 0.33;
    var найден = 0;
    for (var i = 0; i < rows.length; i++) {
      if (rows[i].getBoundingClientRect().top <= порог) найден = i;
    }
    if (найден === текущий) return;
    текущий = найден;
    for (var j = 0; j < links.length; j++) links[j].classList.toggle("is-here", j === найден);
  }

  window.addEventListener("scroll", смотреть, { passive: true });
  window.addEventListener("resize", смотреть);
  смотреть();
})();

/* Просмотр фотографий на весь экран с увеличением.
   Нативный <dialog>: Esc, ловушка фокуса и подложка достаются даром.

   Масштаб держим на самой картинке трансформацией, а не размерами: так
   браузер не пересчитывает раскладку на каждый шаг колеса, и увеличение
   идёт плавно даже на слабой машине. */
(function () {
  "use strict";

  var gal = document.querySelector("[data-gal]");
  var box = document.querySelector("[data-lb]");
  if (!gal || !box || typeof box.showModal !== "function") return;

  var cells = Array.prototype.slice.call(gal.querySelectorAll("[data-gal-open]"));
  if (!cells.length) return;

  var img    = box.querySelector("[data-lb-img]");
  var stage  = box.querySelector("[data-lb-stage]");
  var count  = box.querySelector("[data-lb-count]");
  var cap    = box.querySelector("[data-lb-cap]");
  var strip  = box.querySelector("[data-lb-thumbs-strip]");
  var prevBtn = box.querySelector("[data-lb-prev]");
  var nextBtn = box.querySelector("[data-lb-next]");

  /* Лента миниатюр собирается из той же галереи: второй список адресов
     разошёлся бы с первым при первой же правке. */
  var thumbs = cells.map(function (c, i) {
    var b = document.createElement("button");
    b.type = "button";
    b.className = "lb__thumb";
    b.setAttribute("aria-label", "Фотография " + (i + 1));
    var t = document.createElement("img");
    var src = c.querySelector("img");
    t.src = src ? src.getAttribute("src") : c.getAttribute("data-gal-src");
    t.alt = "";
    t.loading = "lazy";
    b.appendChild(t);
    b.addEventListener("click", function () { показать(i); });
    strip.appendChild(b);
    return b;
  });

  var текущий = 0;
  var масштаб = 1, сдвигX = 0, сдвигY = 0;
  var тянем = false, стартX = 0, стартY = 0;

  function применить(плавно) {
    img.style.transition = плавно ? "transform .28s var(--ease, ease)" : "none";
    img.style.transform = "translate(" + сдвигX + "px," + сдвигY + "px) scale(" + масштаб + ")";
    box.dataset.zoom = масштаб > 1 ? "on" : "off";
  }

  function сброс() {
    масштаб = 1; сдвигX = 0; сдвигY = 0;
    применить(false);
  }

  function показать(i) {
    /* По кругу не листаем: у образца на краях стрелка гаснет, и человек
       видит, что список кончился. */
    if (i < 0 || i >= cells.length) return;
    текущий = i;
    var c = cells[текущий];
    img.src = c.getAttribute("data-gal-src");
    img.alt = (c.querySelector("img") || {}).alt || "";
    cap.textContent = c.getAttribute("data-gal-cap") || "";
    count.textContent = (текущий + 1) + " / " + cells.length;
    thumbs.forEach(function (t, k) { t.setAttribute("aria-current", k === текущий ? "true" : "false"); });
    if (thumbs[текущий] && thumbs[текущий].scrollIntoView) {
      thumbs[текущий].scrollIntoView({ block: "nearest", inline: "center" });
    }
    prevBtn.disabled = текущий === 0;
    nextBtn.disabled = текущий === cells.length - 1;
    сброс();
  }

  cells.forEach(function (c, i) {
    c.addEventListener("click", function () { показать(i); box.showModal(); });
  });

  box.querySelector("[data-lb-close]").addEventListener("click", function () { box.close(); });
  prevBtn.addEventListener("click", function () { стоп(); показать(текущий - 1); });
  nextBtn.addEventListener("click", function () { стоп(); показать(текущий + 1); });

  /* Кнопка увеличения в панели — то же, что двойное нажатие по снимку. */
  box.querySelector("[data-lb-zoom]").addEventListener("click", function () {
    масштаб = масштаб > 1 ? 1 : 2;
    сдвигX = 0; сдвигY = 0;
    применить(true);
  });

  /* Слайдшоу: листает само, пока не остановят. Останавливается и при ручном
     переходе — иначе человек листает, а через секунду его сносит дальше. */
  var показ = null;
  var playBtn = box.querySelector("[data-lb-play]");

  function стоп() {
    if (!показ) return;
    clearInterval(показ); показ = null;
    playBtn.setAttribute("aria-pressed", "false");
  }

  playBtn.addEventListener("click", function () {
    if (показ) { стоп(); return; }
    playBtn.setAttribute("aria-pressed", "true");
    показ = setInterval(function () {
      /* На последнем снимке начинаем сначала: у показа нет конца, иначе он
         молча замирает и кнопка врёт, что всё ещё идёт. */
      показать(текущий === cells.length - 1 ? 0 : текущий + 1);
    }, 3200);
  });

  /* Во весь экран — средствами браузера, своего «псевдополного» не делаем:
     на нём остаётся адресная строка, и это видно. */
  box.querySelector("[data-lb-full]").addEventListener("click", function () {
    if (document.fullscreenElement) { document.exitFullscreen(); return; }
    if (box.requestFullscreen) box.requestFullscreen();
  });

  /* Миниатюры прячутся и возвращаются: на невысоком экране они забирают
     восьмую часть высоты, а иногда нужен только снимок. */
  var thumbsBtn = box.querySelector("[data-lb-thumbs]");
  thumbsBtn.addEventListener("click", function () {
    var on = box.dataset.thumbs !== "off";
    box.dataset.thumbs = on ? "off" : "on";
    thumbsBtn.setAttribute("aria-pressed", on ? "false" : "true");
  });

  /* Клик по подложке закрывает, клик по самой картинке — нет. */
  box.addEventListener("click", function (e) {
    if (e.target === box || e.target === stage) box.close();
  });

  document.addEventListener("keydown", function (e) {
    if (!box.open) return;
    if (e.key === "ArrowLeft") показать(текущий - 1);
    if (e.key === "ArrowRight") показать(текущий + 1);
  });

  /* Колесо: увеличение к точке под курсором, а не к центру — иначе на
     большом снимке нужная деталь уезжает за край. */
  stage.addEventListener("wheel", function (e) {
    e.preventDefault();
    var было = масштаб;
    масштаб = Math.min(4, Math.max(1, масштаб * (e.deltaY < 0 ? 1.18 : 1 / 1.18)));
    if (масштаб === было) return;

    var r = img.getBoundingClientRect();
    var кx = e.clientX - (r.left + r.width / 2);
    var кy = e.clientY - (r.top + r.height / 2);
    var k = масштаб / было;
    сдвигX = сдвигX - кx * (k - 1);
    сдвигY = сдвигY - кy * (k - 1);
    if (масштаб === 1) { сдвигX = 0; сдвигY = 0; }
    применить(false);
  }, { passive: false });

  img.addEventListener("dblclick", function () {
    масштаб = масштаб > 1 ? 1 : 2;
    сдвигX = 0; сдвигY = 0;
    применить(true);
  });

  /* Перетаскивание увеличенного снимка. Указатели, а не мышь: одним кодом
     работает и палец, и трекпад. */
  img.addEventListener("pointerdown", function (e) {
    if (масштаб === 1) return;
    тянем = true; стартX = e.clientX - сдвигX; стартY = e.clientY - сдвигY;
    img.setPointerCapture(e.pointerId);
  });
  img.addEventListener("pointermove", function (e) {
    if (!тянем) return;
    сдвигX = e.clientX - стартX; сдвигY = e.clientY - стартY;
    применить(false);
  });
  img.addEventListener("pointerup", function () { тянем = false; });

  box.addEventListener("close", function () { стоп(); сброс(); });
})();

/* ---------- Плашка про cookie ----------
   Уведомление, а не диалог: фокус не перехватываем и страницу не блокируем.
   Показываем ПОСЛЕ полной загрузки — до неё плашка лежит под hidden, чтобы
   не влезать в замер LCP и не дёргать раскладку.

   Ответ запоминаем в localStorage, а не в куке: ставить куку ради согласия
   на куки — ровно то, от чего мы человека предупреждаем. Хранилище может
   быть недоступно (приватное окно, запрет на данные сайта) — тогда плашка
   покажется снова, и это лучше, чем упавший скрипт.

   КУДА ВЕШАТЬ СЧЁТЧИК. Появится Яндекс.Метрика — её код подключать на
   событие artdom:cookie-ok, а не в <head>. Событие приходит в момент
   согласия и сразу при загрузке, если человек согласился раньше. Без этого
   счётчик грузится до нажатия, и кнопка ничего не решает — та самая ошибка,
   что у обоих образцов, с которых снималось построение. */
(function () {
  var плашка = document.querySelector("[data-cookie]");
  if (!плашка) return;

  var КЛЮЧ = "artdom-cookie-ok";

  function прочитать() {
    try { return localStorage.getItem(КЛЮЧ); } catch (e) { return null; }
  }
  function записать(значение) {
    try { localStorage.setItem(КЛЮЧ, значение); } catch (e) { /* приватное окно — переживём */ }
  }
  function согласие() {
    document.dispatchEvent(new CustomEvent("artdom:cookie-ok"));
  }

  /* Уже отвечал — плашки нет вовсе. Согласие поднимает счётчик, отказ не
     делает ничего: ни счётчика, ни повторного вопроса. */
  var прежний = прочитать();
  if (прежний) {
    if (прежний === "yes" || прежний === "1") согласие();
    return;
  }

  function показать() {
    плашка.hidden = false;
    /* Кадр между снятием hidden и классом: без него переход не проигрывается,
       браузер склеивает оба изменения в одну отрисовку. */
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { плашка.classList.add("is-in"); });
    });
  }

  if (document.readyState === "complete") {
    setTimeout(показать, 600);
  } else {
    window.addEventListener("load", function () { setTimeout(показать, 600); });
  }

  function убрать() {
    плашка.classList.remove("is-in");
    /* Убираем из дерева только после ухода, иначе исчезает рывком. */
    setTimeout(function () { плашка.hidden = true; }, 400);
  }

  var да = плашка.querySelector("[data-cookie-ok]");
  if (да) {
    да.addEventListener("click", function () {
      записать("yes");
      согласие();
      убрать();
    });
  }

  /* Кнопки отказа в разметке нет, пока в настройках не задан счётчик:
     отказываться было бы не от чего. */
  var нет = плашка.querySelector("[data-cookie-no]");
  if (нет) {
    нет.addEventListener("click", function () {
      записать("no");
      убрать();
    });
  }
})();

/* ---------- Шапка уезжает при прокрутке вниз ----------
   Просьба заказчика 13.09.2026: на узком экране логотип и бургер лежат
   поверх текста, потому что шапка фиксированная и без подложки. Листаешь
   вниз — уезжают, листаешь вверх — возвращаются.

   Порог в 6 пикселей обязателен: без него класс дёргается на каждом
   микродвижении пальца и шапка мигает. Верхние 80 пикселей страницы
   считаются «началом» — там шапка видна всегда, иначе на самом верху
   она пряталась бы от короткого рывка.

   Считаем в requestAnimationFrame, а не в самом обработчике: событие
   прокрутки приходит чаще кадра, и чтение scrollY в нём заставляет
   браузер пересчитывать раскладку. */
(function () {
  var root = document.documentElement;
  var порог = 6;
  var верх = 80;
  var прошлый = window.scrollY;
  var ждём = false;

  function считать() {
    ждём = false;
    var y = window.scrollY;

    /* Меню открыто — не трогаем вовсе. */
    if (root.classList.contains("menu-open")) { прошлый = y; return; }

    /* Отскок за край (iOS отдаёт отрицательный scrollY) — это не движение. */
    if (y < 0) { прошлый = 0; return; }

    if (y <= верх) { root.classList.remove("hdr-away"); прошлый = y; return; }

    var шаг = y - прошлый;
    if (Math.abs(шаг) < порог) return;          /* дрожь пальца пропускаем */
    root.classList.toggle("hdr-away", шаг > 0);
    прошлый = y;
  }

  window.addEventListener("scroll", function () {
    if (ждём) return;
    ждём = true;
    requestAnimationFrame(считать);
  }, { passive: true });

  считать();
})();
