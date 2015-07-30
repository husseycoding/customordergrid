var selected = Class.create({
    initialize: function() {
        $("row_customordergrid_configure_columnsorder").hide();
        $("row_customordergrid_configure_columnswidth").hide();
        if ($("customordergrid_configure_columnsorder").value) {
            this.userselected = $("customordergrid_configure_columnsorder").value.split(",");
        } else {
            this.userselected = new Array();
        }
        this.getWidthArray();
        this.getText();
        Event.observe($("customordergrid_configure_columns"), "change", this.updateForm.bindAsEventListener(this));
        $('row_customordergrid_configure_columns').insert({ after: '<tr><td class="label">Column Display Order <br><br><div style="font-size: 11px; width: 160px;"> (<b>Drag</b> and <b>Drop</b> column name to move order on the grid) <br> (Input column width in the box on the right. It will default to 80px if left blank)</div></td><td id="displaycolumnorder" class="value"></td></tr>' });
        this.outputOrder();
        this.updateSortElement();
        this.observeWidthInput();
    },
    addColumn: function(id) {
        if (this.userselected.indexOf(id) == -1) {
            this.userselected.push(id);
        }
        if(typeof this.selectedWidths[id] === 'undefined') {
            this.selectedWidths[id] = "";
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
        this.observeWidthInput();
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
                var widthValue = (typeof this.selectedWidths[s] === 'undefined') ? "" : this.selectedWidths[s];
                html.push('<div class="sortables" ><li class="sortableValues" style="width: 200px; display: inline-block; cursor: grab;">' + this.valuetext[s] +
                '</li><input class="widthInput" type="text" name="' + this.reverseValueText[this.valuetext[s]] + '" value="' + widthValue + '" size="1" style="float: right; display: inline-block;"></div>');
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
        var reverseT = this.reverseValueText;
        Sortable.create('displaycolumnorder',
            {
                tag:'div',
                onChange: function()
                {
                    var liSort = $$('li.sortableValues');
                    var string = '';
                    for (var i = 0, len = liSort.length; i < len; i++) {
                        string += reverseT[liSort[i].innerHTML];
                        if (i < len - 1) string += ',';
                    }
                    $('customordergrid_configure_columnsorder').value = string;
                }
            });
    },
    observeWidthInput: function() {
        $$('.widthInput').invoke('observe', 'keyup', function() {
            var inputWidth = $$('input.widthInput');
            var string = '';
            for (var i = 0, len = inputWidth.length; i < len; i++) {
                string += inputWidth[i].name + ':' + inputWidth[i].value;
                if (i < len - 1) string += ',';
            }
            $('customordergrid_configure_columnswidth').value = string;
        });
    },
    getWidthArray: function() {
        if ($("customordergrid_configure_columnswidth").value) {
            this.selectedWidths = new Array();
            var temp  = $("customordergrid_configure_columnswidth").value.split(",");
            for(var i = 0; i < temp.length; i++){
                var spl = temp[i].split(':');
                this.selectedWidths[spl[0]] = spl[1];
            }

        } else {
            this.selectedWidths = new Array();
        }
    }
});

document.observe("dom:loaded", function() {
    if ($("customordergrid_configure_columnsorder")) {
        var selecteditems = new selected();
    }
});
