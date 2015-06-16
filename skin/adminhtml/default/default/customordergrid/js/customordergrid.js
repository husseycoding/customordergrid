var selected = Class.create({
    initialize: function() {
        $("row_customordergrid_configure_columnsorder").hide();
        if ($("customordergrid_configure_columnsorder").value) {
            this.userselected = $("customordergrid_configure_columnsorder").value.split(",");
        } else {
            this.userselected = new Array();
        }
        this.getText();
        Event.observe($("customordergrid_configure_columns"), "change", this.updateForm.bindAsEventListener(this));
        $('row_customordergrid_configure_columns').insert({ after: '<tr><td class="label">Column Display Order <br> ( Drag and Drop to move order )</td><td id="displaycolumnorder" class="value"></td></tr>' });
        this.outputOrder();
        this.updateSortElement();
    },
    addColumn: function(id) {
        if (this.userselected.indexOf(id) == -1) {
            this.userselected.push(id);
        }
    },
    removeColumn: function(id) {
        var count = 0;
        this.userselected.each(function(s) {
            if (s == id) {
                this.userselected.splice(count, 1);
                throw $break;
            }
            count++;
        }.bind(this));
    },
    updateForm: function() {
        this.checkForm();
        this.outputOrder();
        this.updateSortElement();
    },
    checkForm: function() {
        var selectedoptions = {};
        this.userselected.each(function(s) {
            selectedoptions[s] = true;
        }.bind(this));
        for (var i = 0; i < $("customordergrid_configure_columns").options.length; i++) {
            var value = $("customordergrid_configure_columns").options[i].value;
            if (selectedoptions[value] && !$("customordergrid_configure_columns").options[i].selected) {
                this.removeColumn(value);
            } else if (!selectedoptions[value] && $("customordergrid_configure_columns").options[i].selected) {
                this.addColumn(value);
            }
        }
    },
    getText: function() {
        this.valuetext = {};
        this.reverseValueText = {};
        for (var i = 0; i < $("customordergrid_configure_columns").options.length; i++) {
            this.valuetext[$("customordergrid_configure_columns").options[i].value] = $("customordergrid_configure_columns").options[i].text;
            this.reverseValueText[$("customordergrid_configure_columns").options[i].text] = $("customordergrid_configure_columns").options[i].value;
        }
    },
    outputOrder: function() {
        var html = new Array();
        var hidden = new Array();
        this.userselected.each(function(s) {
            if (this.valuetext[s]) {
                html.push('<li class="sortables" >' + this.valuetext[s] + '</li>');
                hidden.push(s);
            }
        }.bind(this));
        hidden = hidden.join(",");
        html = html.join('');
        $("customordergrid_configure_columnsorder").value = hidden;
        $("displaycolumnorder").update(html);
        this.createSortable();
    },
    updateSortElement: function() {
        for (var i = 0; i < $("customordergrid_configure_columnsort").options.length; i++) {
            var el = $("customordergrid_configure_columnsort").options[i];
            if (this.userselected.indexOf(el.value) >= 0) {
                el.disabled = false;
            } else if (el.value && el.value != "real_order_id") {
                if (el.selected) {
                    el.selected = false;
                    $("customordergrid_configure_columnsort").options[0].selected  = true;
                }
                el.disabled = true;
            }
        }
    },
    createSortable: function() {
        var localRVT = this.reverseValueText;
        Sortable.create('displaycolumnorder',
            {
                tag:'li',
                onChange: function()
                {
                    var liSort = $$('li.sortables');
                    var string = '';
                    for (var i = 0, len = liSort.length; i < len; i++) {
                            string += localRVT[liSort[i].innerHTML];
                            if (i < len - 1) string += ',';
                    }
                    $('customordergrid_configure_columnsorder').value = string;
                }
            });
    }
});

document.observe("dom:loaded", function() {
    if ($("customordergrid_configure_columnsorder")) {
        var selecteditems = new selected();
    }
});
