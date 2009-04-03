var Finder = { };
Finder.Base = Class.create({
  initialize: function(input) {
    input = $(input);
    this.value = new Element('input', {type:'hidden', name:input.name});
    input.name = '';
    this.span = new Element('span', input);
    this.error = new Element('span');
    this.display = new Element('span');
    this.editor = input.replace(this.span);
    this.newItem = new Element('div');

    this.span.appendChild(this.value);
    this.span.appendChild(this.editor);
    this.span.appendChild(this.error);
    this.span.appendChild(this.newItem);
    this.error.hide();
    this.newItem.hide();

    new Form.Element.Observer(this.editor, 1, this.verify.bind(this));
  },

  editData: function() {
    this.display.replace(this.editor);
    this.editor.activate();
    new Form.Element.Observer(this.editor, 1, this.verify.bind(this));
  },

  showData: function(data) {
    if (data) {
      this.error.hide();
      this.newItem.hide();
      this.editor.replace(this.display);
      this.handleData(data);
      this.span.addClassName('valid');
      Event.observe(this.display, 'click', this.editData.bind(this));
    } else {
      this.span.addClassName('invalid');
      params = { };
      params[this.param] = this.editor.value;
      new Ajax.Updater(this. newItem, this.newUrl, {parameters: params, onSuccess: this.newItem.show.bind(this.newItem)});
    }
  },

  showError: function(input, value) {
    this.newItem.hide();
    this.span.addClassName('error');
    var check = new Element('button', {value:'Try again'});
    this.error.update("Connection error.");
    this.error.appendChild(check);
    Event.observe(check, 'click', this.verify.bind(this, input, value));
  },

  verify: function(input, value) {
    this.error.update();
    this.span.removeClassName('error');
    this.span.removeClassName('invalid');
    this.span.removeClassName('valid');
    params = { };
    params[this.param] = this.editor.value;
    new Ajax.Request(this.searchUrl+'.json', {
      parameters: params,
      onSuccess: (function(response) {
        var data = response.headerJSON;
	this.showData(data);
      }).bind(this),
      onFailure: (function() {
        this.showError(input, value);
      }).bind(this)
    });
  },

  handleData: function(data) {},
});

Finder.Person = Class.create(Finder.Base, {
  handleData: function(data) {
    this.value.value = data.Person.firstName+' '+data.Person.lastName;
    this.display.update(this.value.value);
  },

  param: 'data[Person][name]',
  newUrl: '/people/add',
  searchUrl: '/people/search'
});

Finder.Card = Class.create(Finder.Base, {
  handleData: function(data) {
    this.value.value = data.Identification.card_num;
    this.display.update(data.Person.lastName+', '+data.Person.firstName);
  },

  param: 'data[Identification][card_num]',
  newUrl: '/identifications/add',
  searchUrl: '/identifications/search'

});

