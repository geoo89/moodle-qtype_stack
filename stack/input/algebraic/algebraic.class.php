<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A basic text-field input.
 *
 * @copyright  2012 University of Birmingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stack_algebraic_input extends stack_input {

    protected $extraoptions = array(
        'hideanswer' => false,
        'simp' => false,
        'rationalized' => false,
        'allowempty' => false,
        'nounits' => false,
        'align' => 'left',
        'consolidatesubscripts' => false,
        'checkvars' => 0,
        'validator' => false
    );

    public function render(stack_input_state $state, $fieldname, $readonly, $tavalue) {

        if ($this->errors) {
            return $this->render_error($this->errors);
        }

        $size = $this->parameters['boxWidth'] * 0.9 + 0.1;
        $attributes = array(
            'type'  => 'text',
            'name'  => $fieldname,
            'id'    => $fieldname,
            'size'  => $this->parameters['boxWidth'] * 1.1,
            'style' => 'width: '.$size.'em',
            'autocapitalize' => 'none',
            'spellcheck'     => 'false',
            'class' => 'algebraic',
        );
        if ($this->extraoptions['align'] === 'right') {
            $attributes['class'] = 'algebraic-right';
        }

        $value = $this->contents_to_maxima($state->contents);
        if ($value == 'EMPTYANSWER') {
            // Active empty choices don't result in a syntax hint again (with that option set).
            $attributes['value'] = '';
        } else if ($this->is_blank_response($state->contents)) {
            $field = 'value';
            if ($this->parameters['syntaxAttribute'] == '1') {
                $field = 'placeholder';
            }
            $attributes[$field] = $this->parameters['syntaxHint'];
        } else {
            $attributes['value'] = $value;
        }

        if ($readonly) {
            $attributes['readonly'] = 'readonly';
        }

        return html_writer::empty_tag('input', $attributes);
    }

    public function add_to_moodleform_testinput(MoodleQuickForm $mform) {
        $mform->addElement('text', $this->name, $this->name, array('size' => $this->parameters['boxWidth']));
        $mform->setDefault($this->name, $this->parameters['syntaxHint']);
        $mform->setType($this->name, PARAM_RAW);
    }

    /**
     * Return the default values for the parameters.
     * Parameters are options a teacher might set.
     * @return array parameters` => default value.
     */
    public static function get_parameters_defaults() {
        return array(
            'mustVerify'         => true,
            'showValidation'     => 1,
            'boxWidth'           => 15,
            'insertStars'        => 0,
            'syntaxHint'         => '',
            'syntaxAttribute'    => 0,
            'forbidWords'        => '',
            'allowWords'         => '',
            'forbidFloats'       => true,
            'lowestTerms'        => true,
            'sameType'           => true,
            'options'            => '');
    }

    /**
     * Each actual extension of this base class must decide what parameter values are valid
     * @return array of parameters names.
     */
    public function internal_validate_parameter($parameter, $value) {
        $valid = true;
        switch($parameter) {
            case 'boxWidth':
                $valid = is_int($value) && $value > 0;
                break;
        }
        return $valid;
    }

    /**
     * @return string the teacher's answer, displayed to the student in the general feedback.
     */
    public function get_teacher_answer_display($value, $display) {
        if ($this->extraoptions['hideanswer']) {
            return '';
        }
        if (trim($value) == 'EMPTYANSWER') {
            return stack_string('teacheranswerempty');
        }
        $cs = stack_ast_container::make_from_teacher_source($value, '', new stack_cas_security());
        $cs->set_nounify(0);
        $value = $cs->get_inputform(true, 0, true);
        return stack_string('teacheranswershow', array('value' => '<code>'.$value.'</code>', 'display' => $display));
    }
}
