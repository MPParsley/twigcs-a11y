<?php

namespace NdB\TwigCSA11Y\Rules;

use FriendsOfTwig\Twigcs\Rule\AbstractRule;
use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use Twig\Token as TwigToken;

class TabIndex extends AbstractRule implements RuleInterface
{
    /**
     * @var \FriendsOfTwig\Twigcs\Validator\Violation[]
     */
    protected $violations = [];

    /**
     * @param int $severity
     */
    public function __construct($severity)
    {
        parent::__construct($severity);
        $this->violations = [];
    }

    public function check(TokenStream $tokens)
    {
        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === TwigToken::TEXT_TYPE) {
                $matches = [];
                $textToAnalyse = (string) $token->getValue();
                $terminated  = false;
                $tokenIndex = 1;
                while (!$terminated) {
                    $nextToken = $tokens->look($tokenIndex);
                    if ($nextToken->getType() !== TwigToken::ARROW_TYPE) {
                        $textToAnalyse .= (string) $nextToken->getValue();
                    }
                    if ($nextToken->getType() === TwigToken::TEXT_TYPE
                        || $nextToken->getType() === TwigToken::EOF_TYPE
                    ) {
                        $terminated = true;
                    }
                    $tokenIndex++;
                }
                if (preg_match(
                    "/tabindex=['\"]?((?!0|-1).)+['\"\s>]/U",
                    $textToAnalyse,
                    $matches
                )
                ) {
                    /**
                     * @psalm-suppress InternalMethod
                     * @psalm-suppress UndefinedPropertyFetch
                     */
                    $this->addViolation(
                        (string) $tokens->getSourceContext()->getPath(),
                        $token->getLine(),
                        $token->columnno,
                        sprintf(
                            '[A11Y.TabIndex] Invalid \'tabindex\'. Tabindex must be 0 or -1. Found `%1$s`.',
                            trim($matches[0])
                        )
                    );
                }
            }

            $tokens->next();
        }
        return $this->violations;
    }
}
