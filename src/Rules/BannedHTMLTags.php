<?php

namespace NdB\TwigCSA11Y\Rules;

use Allocine\Twigcs\Rule\AbstractRule;
use Allocine\Twigcs\Rule\RuleInterface;
use Twig\Token as TwigToken;
use Twig\TokenStream;

class BannedHTMLTags extends AbstractRule implements RuleInterface
{
    private const BANNED_TAGS = [
        'blink' => '<\/*blink>',
        'marquee' => '<\/*marquee(.)*>'
    ];

    /**
     * @var \Allocine\Twigcs\Validator\Violation[]
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
                foreach (self::BANNED_TAGS as $tag => $test) {
                    $matches = [];
                    if (preg_match($test, $token->getValue(), $matches)) {
                        /**
                         * @psalm-suppress InternalMethod
                         * @psalm-suppress UndefinedPropertyFetch
                         */
                        $this->addViolation(
                            (string) $tokens->getSourceContext()->getPath(),
                            $token->getLine(),
                            $token->columnno,
                            sprintf('Tag \'%1$s\' is dissallowed in %2$s.', $tag, $token->getValue())
                        );
                    }
                }
            }

            $tokens->next();
        }
        return $this->violations;
    }
}