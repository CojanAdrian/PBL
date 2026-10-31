// Runs before the page paints. Marks the document so CSS can tell whether
// scripts and motion are available, and makes sure the preloader can never
// trap a visitor if something fails to load.
(function () {
  var root = document.documentElement;
  root.classList.add("js");
  if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) root.classList.add("motion");
  try {
    if (window.sessionStorage.getItem("bed-seen")) root.classList.add("loaded");
  } catch (e) {}
  setTimeout(function () {
    root.classList.add("loaded");
  }, 6000);
})();
