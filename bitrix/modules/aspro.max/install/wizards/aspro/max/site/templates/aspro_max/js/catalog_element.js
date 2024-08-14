(function (window) {
  window.SetActiveTab = function (wrapperTabs) {
    wrapperTabs.each(function () {
      var _this = $(this);
      if ((_this.hasClass("active") && _this.hasClass("hidden"))) {
        _this.next().find("a").click();
      } else if ($(this).hasClass("hidden")) {
        _this.next().find("a").click();
      }
    });
  };
})(window);