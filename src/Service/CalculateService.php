<?php


namespace App\Service;


use Exception;

class CalculateService
{
    function calculFinal($input) {
        $arrayInput = [];
        $savedString = null;
        //$input = $input;


        // construire un tableau à partir de la string

        for ($i = 0; $i < strlen($input); $i++) {

            // Si l'élément actuel n'est pas numérique ET l'élement précédent n'est pas numérique ET n'est pas égal au '.'
            if (!is_numeric($input[$i]) && !is_numeric($input[$i - 1]) && $input[$i - 1] !== '.') {
                continue;
            }

            // Si la valeur actuelle est numérique ET ( la suivante est numérique OU égale à '.' ) ; Alors on save la string
            if (is_numeric($input[$i]) && isset($input[$i + 1]) && (is_numeric($input[$i + 1]) || $input[$i + 1] === '.')) {
                $savedString .= $input[$i];
            }

            if ($input[$i] === '.') {
                $savedString .= $input[$i];
            }

            // Si la valeur actuelle est numérique et la suivante n'est pas numérique et n'est pas égale au '.' ; Alors on insère la string sauvegardée
            if (is_numeric($input[$i]) && isset($input[$i + 1]) && !is_numeric($input[$i + 1]) && $input[$i + 1] !== '.' ) {
                $savedString .= $input[$i];
                $arrayInput[] = $savedString;
                $savedString = null;
            }

            // Si l'élément n'est pas numérique et n'est pas égale au '.' et ce n'est pas le dernier caractère
            if (!is_numeric($input[$i]) && $input[$i] !== '.' && $i + 1 !== strlen($input)) {
                $arrayInput[] = $input[$i];
                $savedString = null;
            }

            // Si la dernière valeur est numérique alors on vérifie si la chaine de sauvegarde est vide ou non.
            if ($i + 1 === strlen($input) && is_numeric($input[$i]) && empty($savedString)) {
                $arrayInput[] = $input[$i];
            }
            // Si la dernière valeur est numérique et que la chaine de sauvegarde est vide => on concatène
            if ($i + 1 === strlen($input) && is_numeric($input[$i]) && !empty($savedString)) {
                $arrayInput[] = $savedString.$input[$i];
                $savedString = null;
            }

        }

        $tblOperator = $this->getOperators($arrayInput);


        $inputMultDiv = $this->calculateMultiDiv($arrayInput, $tblOperator);
        $tblOperator = $this->getOperators($arrayInput);

        return $this->calculate($inputMultDiv, $tblOperator);
    }

    /**
     * @param $tblInput
     * @param $tblOperator
     * @return false|mixed
     * @throws Exception
     */
    function calculateMultiDiv(&$tblInput, &$tblOperator) {
        foreach($tblOperator as $keyOp => $op) {

            // Si l'opérateur actuel est une divison et qu'il existe dans la table tblOperator
            if ($op === '/' && in_array('/', $tblOperator)) {

                // Si l'élément précedent est numérique ou float ET que l'élement suivant est numérique ou float
                if ((isset($tblInput[$keyOp - 1]) && (is_numeric($tblInput[$keyOp - 1]) || is_float($tblInput[$keyOp - 1])))  && (isset($tblInput[$keyOp + 1]) && (is_numeric($tblInput[$keyOp + 1]) || is_float($tblInput[$keyOp + 1])) )) {
                    // Division par 0 interdite
                    if ((float)$tblInput[$keyOp + 1] === (float)0) {
                        throw new Exception('Divison by 0');
                        //return false;
                    }
                    // Calcul des deux éléments
                    (float)$operation = (float)$tblInput[$keyOp - 1] / (float)$tblInput[$keyOp + 1];
                    // On store ca dans $tblInput à la place du signe '/'
                    $tblInput[$keyOp] = $operation;

                    // Je supprime les éléments qui ont été divisés
                    unset($tblInput[$keyOp - 1]);
                    unset($tblInput[$keyOp + 1]);
                    unset($tblOperator[$keyOp]);

                    // Je réinitialise les index du $tblInput
                    $tblInput = array_values($tblInput);
                    // Vu que les index ont été réinitialisés je réinitialise les operators
                    $tblOperator = $this->getOperators($tblInput);

                    // Appel en recurcive pour calculer la suite des éléments.
                    $tblInput = $this->calculateMultiDiv($tblInput, $tblOperator);

                } else {
                    return false;
                }
            } else if ($op === '*' && in_array('*', $tblOperator)) {

                // Même principe que la block de dessus
                if ((isset($tblInput[$keyOp - 1]) && is_numeric($tblInput[$keyOp - 1])) && (isset($tblInput[$keyOp + 1]) && is_numeric($tblInput[$keyOp + 1]))) {
                    (float)$operation = (float)$tblInput[$keyOp - 1] * (float)$tblInput[$keyOp + 1];
                    $tblInput[$keyOp] = $operation;


                    unset($tblInput[$keyOp - 1]);
                    unset($tblInput[$keyOp + 1]);
                    unset($tblOperator[$keyOp]);

                    $tblInput = array_values($tblInput);
                    $tblOperator = $this->getOperators($tblInput);


                    $tblInput = $this->calculateMultiDiv($tblInput, $tblOperator);
                }else {
                    return false;
                }

            } else {
                continue;
            }
        }

        return $tblInput;
    }

    function operatorMap($val1, $val2, $operator) {
        switch ($operator) {
            case '*':
                return $val1 * $val2;
            case '/':
                return $val1 / $val2;
            case '+':
                return $val1 + $val2;
            case '-':
                return $val1 - $val2;
            default:
                return false;
        }
    }

    function getOperators($arrayInput) {
        $tblOperator = [];

        foreach($arrayInput as $keyOp => $valOp) {
            if ($valOp === '+' || $valOp === '-' || $valOp === '*' || $valOp === '/') {
                $tblOperator[$keyOp] = $valOp;
            }
        }

        return $tblOperator;
    }

    function calculate(&$tblInput, &$tblOperator = [], &$i = 0) {

        foreach($tblOperator as $keyOp => $op) {

            if ($op === '+') {
                if ($i === 0 && isset($tblInput[$keyOp + 1]) && !isset($tblInput[$keyOp - 1]) && (is_numeric($tblInput[$keyOp + 1]) || is_float($tblInput[$keyOp + 1])) ) {
                    $tblInput[$keyOp + 1] = (float)$tblInput[$keyOp + 1];
                    unset($tblInput[$keyOp]);
                    unset($tblOperator[$keyOp]);

                    $i++;
                } else if ((isset($tblInput[$keyOp - 1]) && (is_numeric($tblInput[$keyOp - 1]) || is_float($tblInput[$keyOp - 1]))) && (isset($tblInput[$keyOp + 1]) && (is_numeric($tblInput[$keyOp + 1]) || is_float($tblInput[$keyOp + 1])) )) {
                    (float)$operation = (float)$tblInput[$keyOp - 1] + (float)$tblInput[$keyOp + 1];
                    $tblInput[$keyOp] = $operation;
                    unset($tblInput[$keyOp - 1]);
                    unset($tblInput[$keyOp + 1]);
                    unset($tblOperator[$keyOp]);

                    $tblInput = array_values($tblInput);
                    $tblOperator = $this->getOperators($tblInput);


                    $tblInput = $this->calculate($tblInput, $tblOperator, $i);

                } else {
                    continue;
                }

            } else if ($op === '-') {
                if ($i === 0 && isset($tblInput[$keyOp + 1]) && !isset($tblInput[$keyOp - 1]) && (is_numeric($tblInput[$keyOp + 1]) || is_float($tblInput[$keyOp + 1])) ) {
                    $tblInput[$keyOp + 1] = -(float)$tblInput[$keyOp + 1];
                    unset($tblInput[$keyOp]);
                    unset($tblOperator[$keyOp]);

                    $i++;
                } else if ((isset($tblInput[$keyOp - 1]) && (is_numeric($tblInput[$keyOp - 1]) || is_float($tblInput[$keyOp - 1]))) && (isset($tblInput[$keyOp + 1]) && (is_numeric($tblInput[$keyOp + 1]) || is_float($tblInput[$keyOp + 1])) )) {
                    (float)$operation = (float)$tblInput[$keyOp - 1] - (float)$tblInput[$keyOp + 1];
                    $tblInput[$keyOp] = $operation;
                    unset($tblInput[$keyOp - 1]);
                    unset($tblInput[$keyOp + 1]);

                    unset($tblOperator[$keyOp]);
                    $tblInput = array_values($tblInput);
                    $tblOperator = $this->getOperators($tblInput);


                    $tblInput = $this->calculate($tblInput, $tblOperator, $i);
                } else {
                    continue;
                }
            } else {
                continue;
            }

        }
        return $tblInput;
    }

}