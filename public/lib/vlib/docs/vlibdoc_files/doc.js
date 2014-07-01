//Redirect selected version to appropriate Perl version
function perlVersion(f) {
  var vopt = f.version.selectedIndex;
  var ver = f.version.options[vopt].value;
  window.location.href = '/perl'+ver+'/';
}

