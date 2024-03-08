((Drupal, drupalSettings, $, once) => {
  Drupal.behaviors.accountAutocomplete = {
    attach(context) {
      const accountsOriginal = drupalSettings.accounts.list
      const accountList = Object.values(drupalSettings.accounts.list)
      const accounts = Array.prototype.concat.apply([], accountList);

      once(
        'accountAutocomplete',
        '.account-autocomplete',
        context,
      ).forEach((input) => {
        const $input = $(input)
        $input.autocomplete({ source: accounts })
        const description = $(`#${$input.attr('aria-describedby')}`)
        description.append(createCollapsibleList(accountsOriginal, description))
      });
    },
  };

  const createCollapsibleList = (_list) => {
    _list = Object.keys(_list).map((header) => {
      var l = _list[header].map(i => `<p>${i}</p>`).join('\n')
      return `<h6>${header}</h6>\n${l}`
    }).join('\n')

    const list = $('<div class="accounts-list-helper"></div>')
    const link = $('<b class="accounts-list--link" style="cursor: pointer;">Accounts List</b>')
    const content = $('<div class="accounts-list--content"></div>').append(_list)
    content.hide()
    list.append(link)
    list.append(content)

    $(link).click(function(){
      $(content).slideToggle();
    })

    return list
  }

})(Drupal, drupalSettings, jQuery, once);
