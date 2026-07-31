(function(){
  function normalizeLabel(rawKey){
    var key = String(rawKey || '').toLowerCase();
    key = key.replace(/^typography[_-]?/, '');
    var map = {
      'h1':'h1','h2':'h2','h3':'h3','h4':'h4','h5':'h5','h6':'h6',
      'p':'p','body':'body'
    };
    return map[key] || key.replace(/_/g,'-');
  }

  function relabelInRoot(root){
    var scope = root && root.querySelectorAll ? root : document;
    var selects = scope.querySelectorAll('select.elementor-control-font-family');
    selects.forEach(function(select){
      var changed = false;
      var options = select.querySelectorAll('option[value^="var(--cwp-font-family-"]');
      options.forEach(function(opt){
        var val = opt.getAttribute('value') || '';
        var m = val.match(/^var\(--cwp-font-family-([a-z0-9_-]+)\)$/i);
        if(!m){ return; }
        var pretty = normalizeLabel(m[1]);
        if (opt.textContent !== pretty) {
          opt.textContent = pretty;
          changed = true;
        }
      });

      // Fix selected label shown in Select2 / rendered control.
      var selected = select.options && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
      if (selected) {
        var selectedPretty = selected.textContent || '';
        var wrapper = select.closest('.elementor-control-input-wrapper') || select.parentElement;
        if (wrapper) {
          var rendered = wrapper.querySelector('.select2-selection__rendered');
          if (rendered && rendered.textContent !== selectedPretty) {
            rendered.textContent = selectedPretty;
          }
        }
      }

      // Notify Select2 to refresh when option labels changed.
      if (changed && window.jQuery) {
        window.jQuery(select).trigger('change.select2');
      }
    });
  }

  function runRelabel(){
    relabelInRoot(document);
  }

  // Initial + delayed passes for first load.
  document.addEventListener('DOMContentLoaded', function(){
    runRelabel();
    setTimeout(runRelabel, 120);
    setTimeout(runRelabel, 350);
    setTimeout(runRelabel, 800);
  });

  // React to panel UI re-renders.
  var mo = new MutationObserver(function(mutations){
    mutations.forEach(function(m){
      if (m.addedNodes && m.addedNodes.length) {
        m.addedNodes.forEach(function(node){
          if (node && node.nodeType === 1) {
            relabelInRoot(node);
          }
        });
      }
    });
  });

  mo.observe(document.documentElement || document.body, { childList: true, subtree: true });

  // Extra hooks for Elementor interactions.
  document.addEventListener('click', function(){ setTimeout(runRelabel, 60); });
  document.addEventListener('change', function(){ setTimeout(runRelabel, 60); });
  if (window.elementor) {
    window.addEventListener('elementor:init', function(){
      setTimeout(runRelabel, 120);
      setTimeout(runRelabel, 400);
    });
  }
})();
