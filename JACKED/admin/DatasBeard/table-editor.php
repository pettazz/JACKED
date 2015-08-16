<?php

    $JACKED->loadDependencies(array('DatasBeard'));

?>

<style type="text/css">

    #typeHelpModal .control-group{
        margin-top:10px;
        margin-bottom: 0px;
    }

    #typeHelpModal .controls input[type=checkbox]{
        margin-left: 25px;
    }

    #typeHelpModal .example{
        transform: scale(0.8);
        opacity: 0.7;
    }

</style>

<script type="text/javascript">

    var sandwichStuff = [
        'Cheese Color',
        'Bread Shape',
        'Tomato Thickness',
        'Mayo Density',
        'Lettuce Aptitude',
        'Meat Arrangement',
        'Pickle Disposition'
    ];

    var rowTemplate = '\
                    <tr>\
                        <td><input type="text" required class="fieldName" placeholder="{placeholder_text}" /></td>\
                        <td>\
                            <select class="fieldType">\
                                <option value="string">String</option>\
                                <option value="integer">Integer</option>\
                                <option value="number">Number</option>\
                                <option value="boolean">Boolean</option>\
                                <option value="any">Any</option>\
                            </select>\
                        </td>\
                        <td width="80px"><input type="checkbox" class="fieldRequired" /></td>\
                        <td width="20px"><button class="btn btn-danger btn-mini field-remove"><i class="icon-white icon-remove"></i></button></td>\
                    </tr>';

    function bindRows(){
        $(".field-remove").click(function(el, ev){
            $(this).parents('tr').remove();

            return false;
        });
    }

    function getSandwichWord(){
        return sandwichStuff[Math.floor(Math.random() * sandwichStuff.length)];
    }

    $(document).ready(function(){
        if(<?php echo (isset($table) && !($table === null))? 'true' : 'false'; ?>){
            var existingSchema = <?php echo (isset($table))? $table['schema'] : '""'; ?>;

            $.each(existingSchema.properties, function(propName, prop){
                var newRow = $(rowTemplate.replace('{placeholder_text}', getSandwichWord()));
                newRow.find('.fieldName').val(propName);
                newRow.find('.fieldType').val(prop.type);
                if($.inArray(propName, existingSchema.required) >= 0){
                    newRow.find('.fieldRequired').prop('checked', true);
                }

                $('.schema-editor tbody').append(newRow);
            });

            $('form#tableEditor').find('input[name=manage_handler]').val('table-edit-handler');
            $('form#tableEditor').find('input[name=inputTableId]').val('<?php echo (isset($table))? $table['uuid'] : ''; ?>');
        }else{
            $('.schema-editor tbody').append($(rowTemplate.replace('{placeholder_text}', getSandwichWord())));            
        }

        bindRows();

        $(".field-add").click(function(el, ev){
            $('.schema-editor tbody').append($(rowTemplate.replace('{placeholder_text}', getSandwichWord())));
            bindRows();

            return false;
        });

        $('#typeHelpLink').click(function(el, ev){
            $('#typeHelpModal').modal({
                keyboard: true,
                show: true
            });
        });

        $('form#tableEditor').submit(function(el, ev){
            var schema = {
                title: $('#inputTableName').val() + ' Schema',
                type: "object",
                additionalProperties: false,
                required: [],
                properties: {}
            };

            $('.schema-editor tbody tr').each(function(){
                schema.properties[$(this).find('.fieldName').val()] = {type: $(this).find('.fieldType').val()};
                
                if($(this).find('.fieldRequired').prop('checked')){
                    schema.required.push($(this).find('.fieldName').val());
                }
            });

            $("input[name=inputTableSchema]").val(JSON.stringify(schema));
        });
    });

</script>

<div id="typeHelpModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
    <button type="button" style="margin-right:10px;" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <div class="modal-body">
        <p class="lead">Type Definitions</p>
        <form class="form-horizontal">
            <fieldset>
                <dl>
                    <dt>String</dt>
                    <dd>A basic text value. Any sequence of characters, which may include letters, numbers, or any typeable characters.</dd>
                    <div class="control-group">
                        <label class="control-label">Example</label>
                        <div class="controls">
                            <input class="example" disabled="disabled" type="text" value="I would like 45 chocolate milks." />  
                        </div>
                    </div>
                    <br />

                    <dt>Integer</dt>
                    <dd>Any positive or negative number, with no decimal places.</dd>
                    <div class="control-group">
                        <label class="control-label">Example</label>
                        <div class="controls">
                            <input class="example" disabled="disabled" type="text" value="2134" /> 
                        </div>
                    </div>
                    <br />

                    <dt>Number</dt>
                    <dd>Any sort of number, including positive, negative, and decimals.</dd>
                    <div class="control-group">
                        <label class="control-label">Example</label>
                        <div class="controls">
                            <input class="example" disabled="disabled" type="text" value="728.23" /> 
                        </div>
                    </div>
                    <br />

                    <dt>Boolean</dt>
                    <dd>A binary value representing only either True or False.</dd>
                    <div class="control-group">
                        <label class="control-label">Example</label>
                        <div class="controls">
                            <input class="example" disabled="disabled" type="checkbox" checked="checked" /> <span class="example">Hungry for apples?</span>
                        </div>
                    </div>
                    <br />

                    <dt>Any</dt>
                    <dd>Allows any type of input. This essentially disables all type checking and will not restrict this value in any way. This should only be used for extremely special cases, as it circumvents most benefits gained by using this module.</dd>
                    <div class="control-group">
                        <label class="control-label">Example</label>
                        <div class="controls">
                            <textarea class="example" disabled="disabled">[{"name": "bleh"}, {"name": "doge"}]</textarea>
                        </div>
                    </div>
                </dl>
            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<h2><?php echo isset($_POST['table-id'])? 'Edit' : 'Create'; ?> Table</h2>

<form class="form-horizontal" id="tableEditor" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/DatasBeard">
    <input type="hidden" name="manage_handler" value="table-create-handler" />
    <input type="hidden" name="doSave" value="true" />
    <input type="hidden" name="inputTableSchema" value="" />
    <input type="hidden" name="inputTableId" value="" />
    <fieldset>

        <div class="control-group">
            <label class="control-label" for="inputTableName">Table Name</label>
            <div class="controls">
                <input type="text" class="input-xxlarge" required name="inputTableName" id="inputTableName" value="<?php echo isset($table)? $table['name'] : ''; ?>" placeholder="Sandwiches I've Eaten">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">Table Schema</label>
            <div class="controls">
                <div class="alert alert-info">
                    <i class="icon-hand-right"></i> The schema describes the structure of the table; it defines the type and name of the fields that make up the table's rows. Every row that is added to the table or edited must conform to this definition. Enabling the Required checkbox for a field will only allow rows that include a value for that field to be added, but disabling it will allow rows to either include it or leave it blank.
                </div>
            </div>
        </div>

        <div class="span9 offset2">           
            <table class="table schema-editor">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type <a href="#" id="typeHelpLink"><i class="icon-question-sign"></i></a></th>
                        <th width="80px">Required</th>
                        <th width="20px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table> 
            <p class="pull-right">
                <button class="btn btn-success btn-mini field-add"><i class="icon-white icon-plus"></i> Add Field</button>
            </p>
        </div>


        <div class="form-actions pull-right span9">
            <button id="saveTable" type="submit" class="btn btn-primary pull-right" style="margin-left: 10px">Save Table</button>
            <a id="cancelButton" class="pull-right btn" href="<?php echo $JACKED->admin->config->entry_point; ?>module/DatasBeard">Cancel</a>
        </div>
    </fieldset>
</form>