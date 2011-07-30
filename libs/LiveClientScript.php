<?php

/**
 * Live Client Script
 * support for live validation of Nette/Forms
 *
 * @version 0.9
 * @author David Grudl, Radek Ježdík, Marek Dušek
 * @package	Nette\Extras\Live-Form-Validation
 *
 * OPTION VARIABLES (other than InstantClientScript):
 *      $doAlert - javascript code to run when error occures
 *                 use variables:
 *                  - validated_element (control on which error occured)
 *                  - message (error message)
 *
 *      $doRemove - javascript code to run when error has been corrected
 *                  use variable validated_element (control on which error was corrected)
 *
 *      $doAlertOnSubmit - javascript code to run if there is any error after form was submitted or FALSE to ignore
 *
 *      $filledOnSubmit - boolean; whether to validate Form::FILLED operations only after form was submittted
 *
 *      $validateOnKeyUp - boolean; whether to call validation function after JS keyup event
 *
 *      $compressCode - boolean; whether to compress JS code to one line of code
 *
 */

final class LiveClientScript extends Object
{
    /** @var string  JavaScript code */
    public $doAlert = 'addError(validated_element, message)';

    /** @var string  JavaScript code */
    public $doRemove = 'removeError(validated_element)';

    /** @var string  JavaScript code */
    public $doToggle = 'if (element) element.style.display = visible ? "" : "none"';

    /** @var FALSE|string JavaScript code to do when any error occurs on submit (to let user know there is an error, eg. on large forms) */
    public $doAlertOnSubmit = FALSE;    // 'informError(submitter)';

    /** @var bool  validate Form::FILLED operations only on submit */
    public $filledOnSubmit = FALSE;

    /** @var bool  validate while writing (keyup event) */
    public $validateOnKeyUp = TRUE;
    
    /** @var bool  compress JS code to one line */
    public $compressCode = FALSE;

    /** @var string  JavaScript event handler name for validating form on submit */
    public $validateFunction;

    /** @var string  JavaScript event handler name for validating controls on fly */
    public $validateControlFunction;

    /** @var string  JavaScript event handler name */
    public $toggleFunction;

    /** @var string  JavaScript variable name indicating whether was the current form submitted */
    private $validateCalled;

    /** @var string */
    public $validateScript;

    /** @var string */
    public $toggleScript;

    /** @var bool */
    private $central;

    /** @var Form */
    private $form;
    
    /** @var string */
    private $name;



    public function __construct(Form $form)
    {
        $this->form = $form;
        $this->name = str_replace("-", "_", ucfirst($form->getName())); //ucfirst(strtr($form->getUniqueId(), Form::NAME_SEPARATOR, '_'));
        $this->validateFunction = $this->name.'.validate';
        $this->validateControlFunction = $this->name.'.validateControl';
        $this->toggleFunction = $this->name.'.toggle';
        $this->validateCalled = $this->name.'.validateCalled';
    }



    public function enable()
    {
        $this->validateScript = '';
        $this->toggleScript = '';
        $this->central = TRUE;

        foreach ($this->form->getControls() as $control) {
            $script = $this->getValidateScript($control->getRules());
            if ($script) {
                $id = $control->htmlId;
                $setId = $control->getControlPrototype()->id;
                if($setId)
                    $id = $setId;
                if(!($control instanceof RadioList))
                    $this->validateScript .=  "if(this.passed['$id'] && (sender.id=='$id'";
                else
                    $this->validateScript .=  "if(this.passed['$id'] && (sender=='$id'";
                    foreach($control->rules as $rule)
                        if ($rule->type === Rule::CONDITION) { // this is condition
                            $this->validateScript .= " || sender.id=='".$rule->control->htmlId."' ";
                        }
                $this->validateScript .= ")) {\n\t\t";
                if(!($control instanceof RadioList))
                    $this->validateScript .=  "\n\t\tvar validated_element = document.getElementById('$id');\n\t";
                else
                    $this->validateScript .=  "\n\t\tvar validated_element = document.getElementById('$id-0').parentNode.lastElementChild;\n\t";
                $this->validateScript .= "\n\t\t$script \n\t";
                $this->validateScript .= "} \n\t";
            }
            $this->toggleScript .= $this->getToggleScript($control->getRules());

            if ($control instanceof ISubmitterControl && $control->getValidationScope() !== TRUE) {
                $this->central = FALSE;
            }
        }

        if ($this->validateScript || $this->toggleScript)
        {
            foreach ($this->form->getComponents(TRUE, 'Nette\Forms\IFormControl') as $control) {
                $control->getControlPrototype()->onchange("$this->validateControlFunction(this);", TRUE);
                $control->getControlPrototype()->onblur("$this->validateControlFunction(this);", TRUE);
                /*if($control instanceof RadioList) {
                    $control->getControlPrototype()->onchange("$this->validateControlFunction('{$control->getHtmlId()}');", TRUE);
                    $control->getControlPrototype()->onblur("$this->validateControlFunction('{$control->getHtmlId()}');", TRUE);
                }*/
                if($this->validateOnKeyUp && $control instanceof TextBase)
                    $control->getControlPrototype()->onkeyup("$this->validateControlFunction(this, event);", TRUE);;
            }
            foreach ($this->form->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $control) {
                if ($control->getValidationScope()) {
                    $control->getControlPrototype()->onclick("return $this->validateFunction(this)", TRUE);
                }
            }
        }
    }



    /**
     * Generates the client side validation script.
     * @return string
     */
    public function renderClientScript()
    {
        $s = '';
        if ($this->validateScript) {
            $s .= "$this->validateControlFunction = function(sender, e) {\n\t"
            . "if(typeof(sender) != 'string')\n\t\t"
                . "this.passed[sender.id] = true;\n\t"
            . "else\n\t\t"
                . "this.passed[sender] = true;\n\t"
            . "if(e && e.keyCode == 9)\n\t\t"
            . "return true;\n\t"
            . "var element, message, res;\n\t"
            . "var b = true;\n\t"
            //. "switch(sender.id) {"
            . $this->validateScript
            //. "}\n"
            . "return b;\n};\n"
            . "$this->validateCalled = false;\n"
            . "$this->validateFunction = function(submitter) {\n\t var b = true;\n"
            . "\t $this->validateCalled = true;\n";
            $init = "passed : { \n";
            foreach ($this->form->getComponents(TRUE, 'Nette\Forms\IFormControl') as $control)
            {
                $id = $control->htmlId;
                $setId = $control->getControlPrototype()->id;
                if($setId)
                    $id = $setId;
                if(!($control instanceof RadioList))
                    $s .= "\t b = $this->validateControlFunction(document.getElementById('$id')) && b; \n";
                /*else
                    $s .= "\t b = $this->validateControlFunction('$id') && b; \n";*/
                $init .= "\t'$id':false,\n";
            }
            $init = substr($init, 0, strlen($init)-2);
            $init .= "\n}";
            if($this->doAlertOnSubmit)
            {
                $s .= "\tif(b == false) {\n\t\t"
                . $this->doAlertOnSubmit . ";\n\t"
                . "}\n";

            }
            $s .= "\treturn b; \n};\n";
        }

        if ($this->toggleScript) {
            $s .= "$this->toggleFunction = function(sender) {\n\t"
            . "var element, visible, res;\n\t"
            . $this->toggleScript
            . "\n};\n\n"
            . "$this->toggleFunction(null);\n";
        }

        if ($s) {
            $s = "var ".$this->name." = {\n $init };\n".$s;
            if($this->compressCode)
                $s = str_replace(array("\n", "\t"), " ", $s);
            return "<script type=\"text/javascript\">\n"
            . "/* <![CDATA[ */\n"
            . $s
            . "/* ]]> */\n"
            . "</script>";
        }
    }



    private function getValidateScript(Rules $rules, $onlyCheck = FALSE)
    {
        $res = '';
        foreach ($rules as $rule) {
            if (!is_string($rule->operation)) continue;

            if (strcasecmp($rule->operation, 'Nette\Forms\InstantClientScript::javascript') === 0) {
                $res .= "$rule->arg\n\t";
                continue;
            }

            $script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
            if (!$script) continue;

            if (!empty($rule->message)) { // this is rule
                if ($onlyCheck) {
                    $res .= "$script\n\tif (" . ($rule->isNegative ? '' : '!') . "res) { return false; }\n\t";

                } else {
                    $res .= "$script \n\t\t"
                        . "if (" . ($rule->isNegative ? '' : '!') . "res && bb == true) { \n\t\t\t"
                        . "message = " . json_encode((string) vsprintf($rule->control->translate($rule->message), (array) $rule->arg)) . "; "
                        . "$this->doAlert ; \n\t\t\t";
                        if($this->filledOnSubmit)
                            $res .= "bb = !($this->validateCalled ||" . intval(strtolower($rule->operation) != ':filled').");";
                        else
                            $res .= "bb = false;";
                        $res .= "\n\t\t}";
                }
            }

            if ($rule->type === Rule::CONDITION) { // this is condition
                $innerScript = $this->getValidateScript($rule->subRules, $onlyCheck);
                if ($innerScript) {
                    $res .= "$script\n\t\tif (" . ($rule->isNegative ? '!' : '') . "res) {\n\t\t" . str_replace("\n\t\t", "\n\t\t\t", rtrim($innerScript)) . "\n\t\t} else if(bb == true) { $this->doRemove; }\n\t";
                    if (!$onlyCheck && $rule->control instanceof ISubmitterControl) {
                        $this->central = FALSE;
                    }
                }
            }
        }
        if($res)
        {
            $res = <<<EOT
var bb = true;
                $res
                if(bb) {
                    $this->doRemove;
                } else {
                    b = false;
                }
EOT;
        }
        return $res;
    }



    private function getToggleScript(Rules $rules, $cond = NULL)
    {
        $s = '';
        foreach ($rules->getToggles() as $id => $visible) {
            $s .= "visible = true; {$cond}element = document.getElementById('" . $id . "');\n\t"
                . ($visible ? '' : 'visible = !visible; ')
                . $this->doToggle
                . "\n\t";
        }
        foreach ($rules as $rule) {
            if ($rule->type === Rule::CONDITION && is_string($rule->operation)) {
                $script = $this->getClientScript($rule->control, $rule->operation, $rule->arg);
                if ($script) {
                    $res = $this->getToggleScript($rule->subRules, $cond . "$script visible = visible && " . ($rule->isNegative ? '!' : '') . "res;\n\t");
                    if ($res) {
                        $el = $rule->control->getControlPrototype();
                        if ($el->getName() === 'select') {
                            $el->onchange("$this->toggleFunction(this)", TRUE);
                        } else {
                            $el->onclick("$this->toggleFunction(this)", TRUE);
                            //$el->onkeyup("$this->toggleFunction(this)", TRUE);
                        }
                        $s .= $res;
                    }
                }
            }
        }
        return $s;
    }



    private function getValueScript(IFormControl $control)
    {
        $tmp = "element = document.getElementById(" . json_encode($control->getHtmlId()) . ");\n\t\t";
        switch (TRUE) {
        case $control instanceof Checkbox:
            return $tmp . "var val = element.checked;\n\t\t";

        case $control instanceof RadioList:
            return "for (var val=null, i=0; i<" . count($control->getItems()) . "; i++) {\n\t\t\t"
            . "element = document.getElementById(" . json_encode($control->getHtmlId() . '-') . "+i);\n\t\t\t"
            . "if (element.checked) { val = element.value; break; }\n\t\t"
            . "}\n\t";

        default:
            return $tmp . "var val = element.value.replace(/^\\s+|\\s+\$/g, '');\n\t\t";
        }
    }



    private function getClientScript(IFormControl $control, $operation, $arg)
    {
        $operation = strtolower($operation);
        switch (TRUE) {
        case $control instanceof HiddenField || $control->isDisabled():
            return NULL;

        case $operation === ':filled' && $control instanceof RadioList:
            return $this->getValueScript($control) . "res = val !== null;";

        case $operation === ':submitted' && $control instanceof SubmitButton:
            return "element=null; res=sender && sender.name==" . json_encode($control->getHtmlName()) . ";";

        case $operation === ':equal' && $control instanceof MultiSelectBox:
            $tmp = array();
            foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
                $tmp[] = "element.options[i].value==" . json_encode((string) $item);
            }
            $first = $control->isFirstSkipped() ? 1 : 0;
            return "element = document.getElementById(" . json_encode($control->getHtmlId()) . ");\n\tres = false;\n\t\t"
                . "for (var i=$first;i<element.options.length;i++)\n\t\t\t"
                . "if (element.options[i].selected && (" . implode(' || ', $tmp) . ")) { res = true; break; }";

        case $operation === ':filled' && $control instanceof SelectBox:
            return "element = document.getElementById(" . json_encode($control->getHtmlId()) . ");\n\t\t"
                . "res = element.selectedIndex >= " . ($control->isFirstSkipped() ? 1 : 0) . ";";

        case $operation === ':filled' && $control instanceof TextInput:
            return $this->getValueScript($control) . "res = val!='' && val!=" . json_encode((string) $control->getEmptyValue()) . ";";

        case $operation === ':minlength' && $control instanceof TextBase:
            return $this->getValueScript($control) . "res = val.length>=" . (int) $arg . ";";

        case $operation === ':maxlength' && $control instanceof TextBase:
            return $this->getValueScript($control) . "res = val.length<=" . (int) $arg . ";";

        case $operation === ':length' && $control instanceof TextBase:
            if (!is_array($arg)) {
                $arg = array($arg, $arg);
            }
            return $this->getValueScript($control) . "res = " . ($arg[0] === NULL ? "true" : "val.length>=" . (int) $arg[0]) . " && "
                . ($arg[1] === NULL ? "true" : "val.length<=" . (int) $arg[1]) . ";";

        case $operation === ':email' && $control instanceof TextBase:
            return $this->getValueScript($control) . 'res = /^[^@]+@[^@]+\.[a-z]{2,6}$/i.test(val);';

        case $operation === ':url' && $control instanceof TextBase:
            return $this->getValueScript($control) . 'res = /^.+\.[a-z]{2,6}(\\/.*)?$/i.test(val);';

        case $operation === ':regexp' && $control instanceof TextBase:
            if (strncmp($arg, '/', 1)) {
                throw new InvalidStateException("Regular expression '$arg' must be JavaScript compatible.");
            }
            return $this->getValueScript($control) . "res = $arg.test(val);";

        case $operation === ':integer' && $control instanceof TextBase:
            return $this->getValueScript($control) . "res = /^-?[0-9]+$/.test(val);";

        case $operation === ':float' && $control instanceof TextBase:
            return $this->getValueScript($control) . "res = /^-?[0-9]*[.,]?[0-9]+$/.test(val);";

        case $operation === ':range' && $control instanceof TextBase:
            return $this->getValueScript($control) . "res = " . ($arg[0] === NULL ? "true" : "parseFloat(val)>=" . json_encode((float) $arg[0])) . " && "
                . ($arg[1] === NULL ? "true" : "parseFloat(val)<=" . json_encode((float) $arg[1])) . ";";

        case $operation === ':filled' && $control instanceof FormControl:
            return $this->getValueScript($control) . "res = val!='';";

        case $operation === ':valid' && $control instanceof FormControl:
            return $this->getValueScript($control) . "res = function(){\n\t" . $this->getValidateScript($control->getRules(), TRUE) . "return true; }();";

        case $operation === ':equal' && $control instanceof FormControl:
            if ($control instanceof Checkbox) $arg = (bool) $arg;
            $tmp = array();
            foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
                if ($item instanceof IFormControl) { // compare with another form control?
                    $tmp[] = "val==function(){var element;" . $this->getValueScript($item). "return val;}()";
                } else {
                    $tmp[] = "val==" . json_encode($item);
                }
            }
            return $this->getValueScript($control) . "res = (" . implode(' || ', $tmp) . ");";
        }
    }



    public static function javascript()
    {
        return TRUE;
    }

}
