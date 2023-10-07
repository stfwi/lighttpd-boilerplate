
const navigation_entries = [
  {
    label: "Dashboard",
    link: "/",
    icon_class: "fa-home"
  },
  {
    label: "Links",
    link: "/links",
    icon_class: "fa-globe",
    sub_navigation: true
  },
  {
    label: "Threads",
    link: "/threads",
    icon_class: "fa-comments",
    sub_navigation: true
  },
  {
    label: "Documentation",
    link: "/docs/",
    icon_class: "fa-book",
    sub_navigation: true
  },
  {
    label: "Tools",
    link: "/tools",
    icon_class: "fa-cogs",
    sub_navigation: true
  },
  {
    label: "Info",
    link: "/docs/info",
    icon_class: "fa-info",
    sub_navigation: true
  }
];

$(function(){
  $("<ul>").appendTo($("nav.main-menu"));
  const $menu = $("nav.main-menu ul").first();
  navigation_entries.forEach(item => {
    const $e = $('<li><a href="#"><i class="fa '+(item.icon_class||"")+' fa-2x"></i><span class="nav-text"></span></a></li>')
    $e.find("span.nav-text").text(item.label || "UNDEFINED");
    $e.find("a").attr("href", item.link || "#");
    if(item.sub_navigation) $e.addClass("has-subnav");
    $e.appendTo($menu);
  });
});
