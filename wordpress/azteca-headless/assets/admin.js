(function ($) {
  // Galería (selector múltiple de medios con orden por arrastre)
  $('.az-gal').each(function () {
    var $box = $(this), $list = $box.find('.az-gal__list'), $in = $box.find('input[type=hidden]');
    function sync() { $in.val($list.children().map(function () { return $(this).data('id'); }).get().join(',')); }
    $list.sortable({ update: sync });
    $box.on('click', '.az-gal__rm', function () { $(this).closest('li').remove(); sync(); });
    var frame;
    $box.on('click', '.az-gal__add', function (e) {
      e.preventDefault();
      if (!frame) {
        frame = wp.media({ title: 'Elegir imágenes', button: { text: 'Añadir a la galería' }, multiple: 'add', library: { type: 'image' } });
        frame.on('select', function () {
          frame.state().get('selection').each(function (a) {
            a = a.toJSON();
            if ($list.children('[data-id=' + a.id + ']').length) return;
            var t = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
            $('<li>').attr('data-id', a.id).data('id', a.id).append($('<img>').attr('src', t), '<button type="button" class="az-gal__rm" aria-label="Quitar">×</button>').appendTo($list);
          });
          sync();
        });
      }
      frame.open();
    });
  });

  // Importador
  $('#az-import').on('click', function () {
    var $b = $(this).prop('disabled', true), total = +$b.data('total'), $bar = $('#az-bar'), $log = $('#az-log');
    $('#az-progress').show();
    (function step(i) {
      $.post(AZTECA.ajax, { action: 'azteca_import_step', nonce: AZTECA.nonce, i: i }).done(function (r) {
        if (!r.success) { $log.append('Error: ' + r.data + '\n'); $b.prop('disabled', false); return; }
        if (r.data.done) { $bar.css('width', '100%'); $log.append('\n✔ Importación terminada.'); return; }
        $bar.css('width', Math.round((r.data.next / total) * 100) + '%');
        $log.append((r.data.next) + '/' + total + '  ' + r.data.label + '\n'); $log.scrollTop($log[0].scrollHeight);
        step(r.data.next);
      }).fail(function () { $log.append('Se cortó la conexión en el elemento ' + i + '. Pulsa de nuevo para continuar (lo ya importado se omite).\n'); $b.prop('disabled', false); });
    })(0);
  });
})(jQuery);
