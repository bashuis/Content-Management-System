<?php

function createFormForLang($id, $name = false, $flag = false, $errors, $input)
{
    ?>
    <div class="onlyinput" style="cursor: pointer; background-color: <?php echo (isset($errors['name_' . $id]) || isset($errors['text_' . $id])) ? '#FBC2C4' : '#EEEEEE'; ?>; padding: 10px; font-weight: bold;">
        <?php if( $flag || $name ){ if( $flag ){ ?><img src="<?php echo $flag; ?>" /> <?php } if( $name ){ echo $name; } } ?>
    </div>
    <div>
        <div class="normalrow">
            <label class="required">Naam:</label>
            <input type="text" name="name_<?php echo $id; ?>"<?php Form::doPrevious( $input, 'name_'.$id ); ?>>
            <?php Form::doError( $errors, 'name_'.$id ); ?>
        </div>
        <div class="onlytext">
            <label class="required">Tekst:</label>
        </div>
        <div class="onlyinput">
            <div>
            <?php
                $fck = new FCKEditor('text_'.$id);
                $fck->Height = 400;
                $fck->Width = 600;
                $fck->ToolbarSet = 'Default_preview';

                if( isset( $input['text_'.$id] ) )
                {
                    $fck->Value = $input['text_'.$id];
                }
                $fck->Create();
            ?>
            </div>
            <?php Form::doError( $errors, 'text_'.$id ); ?>
        </div>
    </div>
    <?php
}

function checkInput($lang, $input)
{
    $errors = array();

    if (empty($input['name_'.$lang]))
    {
        $errors['name_'.$lang] = 'Je moet wel een naam voor de pagina invullen!';
    }
    if (empty($input['text_'.$lang]))
    {
        $errors['text_'.$lang] = 'Je moet wel tekst voor de pagina invullen!';
    }

    return $errors;
}