(function () {
    "use strict";

    function updateAppBarState() {
        var appBar = document.querySelector(".mui-web-app-bar");
        if (!appBar) {
            return;
        }
        if (window.pageYOffset > 4) {
            appBar.classList.add("is-scrolled");
        } else {
            appBar.classList.remove("is-scrolled");
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", updateAppBarState);
    } else {
        updateAppBarState();
    }
    window.addEventListener("scroll", updateAppBarState, { passive: true });
})();
