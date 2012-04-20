function getTheme() {
    var theme =  $.data(document.body, 'theme');
    if (theme == null) {
        theme = '';
    }
    else {
        return theme;
    }

    var themestart = window.location.toString().indexOf('?');
    if (themestart == -1) {
        return '';
    }

    var theme = window.location.toString().substring(1 + themestart);
    var url = "js/vendor/jqwidgets/styles/jqx." + theme + '.css';

    if (document.createStyleSheet != undefined) {
        document.createStyleSheet(url);
    }
    else $(document).find('head').append('<link rel="stylesheet" href="' + url + '" media="screen" />');
  
    return theme;
};
