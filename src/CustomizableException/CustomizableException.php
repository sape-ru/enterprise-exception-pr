<?php
namespace MagicPush\EnterpriseException\CustomizableException;

use MagicPush\EnterpriseException\GlobalException;

/**
 * The exception class used for customizing its exceptions with additional properties.
 *
 * There may be any properties for an exception you can imagine and implement in the class successors.
 * For instance there is an implementation of an exception message frontend version already. And there is more -
 * read the EXCEPTIONS_PROPERTIES description and other class elements mentioned in the config for more info.
 * Also it provides you with an ability to specify only an exception code: a message will be taken from
 * the properties config - EXCEPTIONS_PROPERTIES. Read the constructor documentation to know more.
 *
 * The CLASS_SECTION_LIST and CLASS_SECTION_DEFAULT are an addition to let the Parser filtrate classes by sections
 * (read the Parser documentation in its own class file).
 *
 * You must extend this class with your project base exception class (PBEC). All your exceptions classes must extend
 * that PBEC and have their EXCEPTIONS_PROPERTIES configs set up properly. After that you must call the constructor
 * in customizable mode - via passing exception (base) code as the first argument. Otherwise this class will act
 * like the classic \Exception class.
 *
 * Also if you want to use exceptions codes globalization feature or parse your exceptions classes then set up
 * the CLASS_CODE_LIST config in PBEC (read the GlobalException and Parser documentation for more info).
 *
 * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up the exceptions properties config.
 * @see CustomizableException::__construct()            For the constructor documentation.
 * @see CustomizableException::CLASS_SECTION_LIST       For checking / setting certain classes sections.
 * @see CustomizableException::CLASS_SECTION_DEFAULT    For checking / setting the default section.
 * @see Parser::parse()                                 For the customizable exceptions Parser documentation.
 * @see GlobalException::CLASS_CODE_LIST                For checking / setting up the globalization config.
 *
 * @package CustomizableException
 *
 * @author Kirill Ulanovskiy <xerxes.home@gmail.com>
 */
abstract class CustomizableException extends GlobalException
{
    /**
     * @var string A class default section.
     *
     * This value is used for the Parser sections filtering if a class name is not found
     * in the CLASS_SECTION_LIST config.
     *
     * @see CustomizableException::CLASS_SECTION_LIST   For checking / setting certain classes sections.
     * @see Parser::parse()                             For the customizable exceptions Parser documentation.
     */
    const CLASS_SECTION_DEFAULT = '';
    /**
     * @var array|string[] The Parser sections filter config.
     *
     * This config is used by the Parser to filter exceptions classes by sections you can set.
     * Each element of the config represents an exception class (a subclass of the CustomizableException).
     * The element key must be a fully qualified class name (AnyClass::class) or it will be ignored -
     * not found while comparing with a fully qualified class name being checked for filtering.
     * The element value may be any string (or a number) you prefer to use as a section name. Just use the same string
     * in the Parser section filter to include or exclude any class linked to that section.
     * If you want to set a default section to all classes not specified in this config then redefine
     * the CLASS_SECTION_DEFAULT constant.
     *
     * @see CustomizableException::CLASS_SECTION_DEFAULT    For checking / setting the default section.
     * @see Parser::parse()                                 For the customizable exceptions Parser documentation.
     */
    const CLASS_SECTION_LIST = [];

    /**
     * @var array The central CustomizableException config for specifying exceptions' properties.
     *
     * You must set up this config for every subclass of the CustomizableException you would like to throw.
     * Each element of the array represents a class exception with its (int) code
     * (base code if you use globalization feature) as a key and its (array) properties as a value.
     * Some properties may be used during an exception construction, some others - during runtime,
     * some - for static usage. It's up to your imagination only.
     * The CustomizableException already implements usage of the properties:
     * <pre>
     *  * context       => (string) An optional exception context (where it happened; the subject).
     *                      Is stored in an exception $_context property and then added
     *                      to the 'message' config property via the ::getMessageComposed().
     *                      Acts like a system version if replaced during runtime via the ::setContext() - can be
     *                      returned then only via the \Exception finalized methods
     *                      (like ::getCode() or ::__toString()).
     *  * message       => (string) An exception base message (what you usually specify as a message to throw).
     *                      If not set the ::getMessageDefault() string is used instead as a default value.
     *                      It is stored in an exception $_message_base property and then passed to the parent
     *                      constructor combined with other exception message parts via the ::getMessageComposed().
     *                      Acts like a system version if the 'message_fe' config property is set - can be returned
     *                      then only via the \Exception finalized methods (like ::getCode() or ::__toString()).
     *  * message_fe    => (string) An optional frontend version of the base message (the 'message' config property).
     *                      If set it replaces the base message stored in an exception $_message_base property during
     *                      construction (but only after passing the original base message to the parent constructor).
     *                      It is used in the ::getMessageFe().
     *  * show_fe       => (bool) An optional flag to control if the ::getMessageFe() should return the real message.
     *                      If not equal to true the ::getMessageFe() will return the ::getMessageFeStub() instead.
     *                      It is stored (not always) in an exception $_show_fe property during construction.
     * </pre>
     *
     * @see CustomizableException::$_context            For the 'context' config property runtime storage.
     * @see CustomizableException::getMessageComposed() For the full exception message composing algorythm.
     * @see CustomizableException::setContext()         For setting the 'context' config property during runtime.
     * @see CustomizableException::getMessageDefault()  For getting the default 'message' config property.
     * @see CustomizableException::$_message_base       For the 'message' config property runtime storage.
     * @see CustomizableException::getMessageFe()       For the frontend message composing algorythm.
     * @see CustomizableException::getMessageFeStub()   For the stub frontend message composing algorythm.
     * @see CustomizableException::$_show_fe            For the 'show_fe' config property runtime storage.
     * @see CustomizableException::__construct()        For an exception construction algorythm
     *                                                  (also this config usage).
     * @see Parser::parse()                             For the customizable exceptions Parser documentation.
     */
    const EXCEPTIONS_PROPERTIES = [];

    /**
     * @var string A default locale for system messages.
     *
     * It is passed to the ::getL10N() explicitly in the constructor to get translations for the system version
     * of an exception message parts.
     *
     * @see CustomizableException::getL10N()        For the translation mechanism.
     * @see CustomizableException::__construct()    For an exception construction algorythm.
     */
    const L10N_SYSTEM_LOCALE = 'en';


    /**
     * @var string $_context An optional exception context (where it happened; the subject).
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the default value
     *                                                      (the 'context' config property).
     * @see CustomizableException::getContext()             For getting the value.
     * @see CustomizableException::setContext()             For replacing the default value.
     * @see CustomizableException::getMessageComposed()     For usage in the full exception message composing algorythm.
     */
    protected $_context = '';
    /**
     * @var string $_details Optional exception details (what exact value is invalid, what is expected etc.).
     *
     * @see CustomizableException::getMessageComposed() For usage in the full exception message composing algorythm.
     * @see CustomizableException::getDetails()         For getting the value.
     */
    protected $_details = '';
    /**
     * @var string $_message_base An exception base message.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the value
     *                                                      (the 'message' and 'message_fe' config properties).
     * @see CustomizableException::getMessageBase()         For getting the value.
     * @see CustomizableException::getMessageComposed()     For usage in the full exception message composing algorythm.
     */
    protected $_message_base;
    /**
     * @var bool $_show_fe An optional flag to control if the ::getMessageFe() should return the real message.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the value (the 'show_fe' config property).
     * @see CustomizableException::getMessageFe()           For the frontend message composing algorythm.
     * @see CustomizableException::canShowFe()              For getting the value.
     * @see CustomizableException::__construct()            For cases when the the 'show_fe' config property is ignored.
     */
    protected $_show_fe = false;


    /**
     * Returns a class section found in the CLASS_SECTION_LIST class name corresponding element
     * or set in the CLASS_SECTION_DEFAULT otherwise.
     *
     * Initially this method is used by the Parser.
     *
     * @see CustomizableException::CLASS_SECTION_LIST       For checking / setting certain classes sections.
     * @see CustomizableException::CLASS_SECTION_DEFAULT    For checking / setting the default section.
     * @see Parser::parse()                                 For the customizable exceptions Parser documentation.
     *
     * @return string A class section.
     */
    public static function getClassSection(): string
    {
        return static::CLASS_SECTION_LIST[static::class] ?? static::CLASS_SECTION_DEFAULT;
    }

    /**
     * Returns the formatted code of an exception to use in different exception string representations.
     *
     * Initially it returns the GlobalException::getCodeGlobal() as a string and is called in the CusomizableExcpetion
     * methods ::getMessageDefault() and ::getMessageFeStub().
     *
     * @see GlobalException::getCodeGlobal()            For a global code calculation algorythm.
     * @see CustomizableException::getMessageDefault()  For a default exception message composing algorythm.
     * @see CustomizableException::getMessageFeStub()   For the frontend message stub composing algorythm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return string An exception formatted code.
     */
    public static function getCodeFormatted(int $base_code): string
    {
        return (string) static::getCodeGlobal($base_code);
    }

    /**
     * Returns the translation of the $text string based on the $locale.
     *
     * If the $locale is not specified it is assumed that the locale is determined automatically inside this method.
     * For instance a user current session locale might be used. And if the $locale is equal to false it is assumed
     * that no translation is needed.
     *
     * Initially this method is called:
     * * during an exception construction for system and frontend versions of an exception message;
     * * when an exception context is set via the ::setContext();
     * * in the Parser::parse() for the message parts specified in the EXCEPTIONS_PROPERTIES configs.
     *
     * Initially this is a stub method - it just returns the $text without any changes.
     * It is your job to redefine this method and provide it with the translation algorythm you desire.
     * For instance many popular frameworks have such a feature implementation already.
     * Just call the needed function passing it the arguments this method gets.
     *
     * @see CustomizableException::__construct()    For an exception construction algorythm.
     * @see CustomizableException::setContext()     For setting an exception $_context property during runtime.
     * @see Parser::parse()                         For the customizable exceptions Parser documentation.
     *
     * @param string $text The string to translate.
     * @param string|bool $locale [optional] The locale used for the translation.
     * If equals false then no translation is made.
     *
     * @return string The translation of the $text string.
     */
    public static function getL10N(
        string $text,
        /** @noinspection PhpUnusedParameterInspection */
        $locale = ''
    ): string {
        return $text;
    }

    /**
     * Composes all message parts into one full exception message.
     *
     * The full exception message consists of the base message ($message) and any other strings as additions you can
     * specify in the $parts array.
     * This method already supports some $parts which are mentioned here in the param's own description.
     *
     * Initially this method is called:
     * * during an exception construction to compose the system version of the message
     * * in the ::getMessageFe() to compose the frontend version of the message
     * * in the Parser::parse() to compose a localized version of the message for short output
     *
     * @see CustomizableException::__construct()    For an exception construction algorythm.
     * @see CustomizableException::getMessageFe()   For the frontend message composing algorythm.
     * @see Parser::parse()                         For the customizable exceptions Parser documentation.
     *
     * @param string $message The base exception message (usually is enough to describe an exception).
     * @param array $parts [optional] An array of message parts to combine with the $message. Supported parts:
     * <pre>
     *  * context   => (string) [default: ''] An exception context (where it happened; the subject).
     *  * details   => (string) [default: ''] Exception details (what exact value is invalid, what is expected etc.).
     * </pre>
     *
     * @return string The full exception message.
     */
    public static function getMessageComposed(string $message, array $parts = []): string
    {
        $parts += [
            'context' => '',
            'details' => '',
        ];
        $message_constructed = '';

        if ('' !== $parts['context']) {
            $message_constructed .= $parts['context'] . ': ';
        }

        $message_constructed .= $message;

        if ('' !== $parts['details']) {
            $message_constructed .= ' (' . $parts['details'] . ')';
        }

        return $message_constructed;
    }

    /**
     * Returns the frontend message stub with general information about an exception.
     *
     * Initially it is returned by the ::getMessageFe() if an exception $\_show_fe property equals false.
     * That is when you don't want user to see that exception real message.
     *
     * Initially this method uses the ::getCodeFormatted() value to let users know an exception code
     * so they can then send it to support and ask for help.
     * The initial 'error' substring is passed to the ::getL10N() in case
     * if it's OK for your application to show this built-in message localized version to users.
     *
     * Also this method is declared as public to let you call it independently. Just make sure to call it
     * from the right exception class and get the right global code if you use the GlobalException feature.
     *
     * @see CustomizableException::getMessageFe()       For the frontend message composing algorythm.
     * @see CustomizableException::canShowFe()          For getting the $_show_fe property value.
     * @see CustomizableException::getCodeFormatted()   For an exception code formatting algorythm.
     * @see CustomizableException::getL10N()            For the translation mechanism.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return string The frontend message stub.
     */
    public static function getMessageFeStub(int $base_code): string
    {
        return static::getL10N('error') . ' ' . static::getCodeFormatted($base_code);
    }


    /**
     * Returns the default value for an exception base message.
     *
     * Initially this method is called in the constructor if an exception 'message' property
     * is not specified in the EXCEPTIONS_PROPERTIES.
     * Also the returned message is considered as the system version of an exception message
     * (and that no translation is needed).
     *
     * Initially the returned message contains the calling class name and its base and global codes
     * (identical if the GlobalException feature is not used for the calling class).
     * The global code format is determined by the ::getCodeFormatted().
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the 'message' config property.
     * @see CustomizableException::__construct()            For an exception construction algorythm.
     * @see CustomizableException::getCodeFormatted()       For an exception code formatting algorythm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return string An exception default base message to replace the 'message' config property.
     */
    protected static function getMessageDefault(int $base_code): string
    {
        return sprintf(
            'CustomizableException %s (%s::%d)',
            static::getCodeFormatted($base_code),
            static::class,
            $base_code
        );
    }

    /**
     * Returns a message for the case when the $base_code is not found as one of the EXCEPTIONS_PROPERTIES config keys.
     *
     * Initially this method is called in the constructor if the EXCEPTIONS_PROPERTIES config doesn't have the key
     * equal to the $base_code.
     * Also the returned message is considered as the system version of an exception message
     * (and that no translation is needed).
     *
     * Initially the returned message contains the $base_code and the calling class name.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting an exception properties' config
     *                                                      under the $base_code as a key.
     * @see CustomizableException::__construct()            For an exception construction algorythm.
     *
     * @param int $base_code An exception base (or full when not global) code.
     *
     * @return string An exception message for the unknown $base_code.
     */
    protected static function getMessageUnknown(int $base_code): string
    {
        return sprintf(
            'unknown base code %d for CustomizableException (%s)',
            $base_code,
            static::class
        );
    }


    /**
     * CustomizableException constructor.
     *
     * Has two modes to construct an exception:
     * * Classic mode - when the first parameter is not numeric and considered as an exception message; then the second
     * parameter is considered as (int) an exception (base) code. No other exception properties are processed.
     * Like you've called the GlobalException or original \Exception constructor.
     * * Customizable mode - when the first parameter is numeric and considered as an exception obligatory (base) code,
     * the critical data to determine other exception's properties; then the second parameter is considered as (string)
     * an exception "details" part of its message. This is the functionality this class is made for.
     * You can "finalize" this mode by specifying the integer type hint in your extended class redefined constructor
     * for the first parameter.
     *
     * The rest description belongs to the customizable mode only.
     *
     * The customizable exception has system and frontend messages:
     * * The system version is always a real message which you can get by the Exception::getMessage(),
     * Exception::__toString() and other original finalized Exception methods. It is composed by
     * the ::getMessageComposed() where the base message and the context are passed after being processed
     * via the ::getL10N() with the L10N_SYSTEM_LOCALE.
     * * The frontend message is always composed during runtime by the ::getMessageFe(). But the message parts
     * ($\_message_base, $\_context, $\_details and everything else you can add in your extended classes)
     * are determined in the constructor and processed by the ::getL10N() (excluding the $_details) without specifying
     * a locale (assuming that the default locale is your current user session locale or anything else you prefer
     * to use in the ::getL10N() in this case). All these message parts are accessible by their own "getters"
     * (::getContext(), ::getDetails(), ::getMessageBase()) and also the $\_context property can be replaced
     * during runtime via the ::setContext().
     *
     * --
     *
     * Step 1. The $details.
     *
     * The $details argument is stored in the $\_details object property
     * which then can be accessed by the ::getDetails(). It is not translated via the ::getL10N() because of its
     * "unstable" nature.
     * It is assumed that the $\_details value is already translated before passing to the constructor.
     *
     * --
     *
     * Step 2. Checking the EXCEPTIONS_PROPERTIES.
     *
     * If the exception properties are missing (the EXCEPTIONS_PROPERTIES key equal to the $base_code is not found)
     * then the $\_message_base property gets the ::getMessageUnknown() value. Also this message will not be
     * processed via the ::getL10N() with the L10N_SYSTEM_LOCALE (assuming that it was already processed properly
     * in the ::getMessageUnknown()).
     *
     * If the exception properties are found the 'context' config property is stored in the $\_context property.
     *
     * If the 'message' config property is missing then the $\_message_base property gets the ::getMessageDefault()
     * value. Also this message will not be processed via the ::getL10N() with the L10N_SYSTEM_LOCALE
     * (assuming that it was already processed properly in the ::getMessageDefault()).
     *
     * If the 'message' config property is not empty then this 'message' value is stored in the $\_message_base
     * property. The 'show_fe' config property is stored in the $\_show_fe in this case only. It can be accessed by
     * the ::canShowFe().
     *
     * --
     *
     * Step 3. The system version of the exception message is passed to the parent constructor
     * (the ::getMessageBase(), ::getContext(), ::getDetails() are called).
     *
     * --
     *
     * Step 4. The frontend version message parts are prepared
     * (the ::getMessageBase(), ::getContext(), ::getDetails() are called).
     *
     * If the ::canShowFe() returns true and the 'message_fe' config property is not an empty string it replaces
     * the system version of the $\_message_base.
     * Then both the ::getMessageBase() and the ::getContext() results are processed via the ::getL10N()
     * without specifying a locale.
     *
     * @see GlobalException::__construct()                  The parent constructor.
     * @see CustomizableException::getMessageComposed()     For the full exception message composing algorythm.
     * @see CustomizableException::getL10N()                For the translation mechanism.
     * @see CustomizableException::L10N_SYSTEM_LOCALE       For checking / setting the system message locale.
     * @see CustomizableException::getMessageFe()           For the frontend message composing algorythm.
     * @see CustomizableException::getContext()             For getting the $_context property value.
     * @see CustomizableException::getDetails()             For getting the $_details property value.
     * @see CustomizableException::getMessageBase()         For getting the $_message_base property value.
     * @see CustomizableException::setContext()             For resetting the $_context property value.
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For checking / setting up the exceptions properties config.
     * @see CustomizableException::getMessageUnknown()      For getting an "unknown exception" message if
     *                                                      the exception's properties are not found.
     * @see CustomizableException::getMessageDefault()      For getting the default 'message' config property.
     * @see CustomizableException::canShowFe()              For getting the $_show_fe property value.
     *
     * @param int|string $base_code [customizable mode] The base (or full when not global) code for the calling
     *      class exception OR [classic mode] the optional exception message to throw.
     * @param string|int $details [customizable mode] The optional exception details (what exact value is invalid,
     *      what is expected etc.) OR [classic mode] the optional base (or full when not global) code
     *      for the calling class exception .
     * @param \Throwable|null $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(string $base_code = '', string $details = '', \Throwable $previous = null)
    {
        if (!is_numeric($base_code)) { // classic constructor - the first argument is an exception message
            $this->_message_base = $base_code;
            parent::__construct($base_code, (int) $details, $previous);

            return;
        }

        $this->_details = (string) $details;
        if (array_key_exists($base_code, static::EXCEPTIONS_PROPERTIES)) {
            if (empty(static::EXCEPTIONS_PROPERTIES[$base_code]['message'])) {
                $this->_message_base = static::getMessageDefault($base_code);
            } else {
                $this->_show_fe = !empty(static::EXCEPTIONS_PROPERTIES[$base_code]['show_fe']);
                $this->_message_base = (string) static::EXCEPTIONS_PROPERTIES[$base_code]['message'];
            }
            $this->_context = (string) (static::EXCEPTIONS_PROPERTIES[$base_code]['context'] ?? '');
        } else {
            $this->_message_base = static::getMessageUnknown($base_code);
        }

        // the system version of an exception message
        parent::__construct(
            static::getMessageComposed(
                static::getL10N($this->getMessageBase(), static::L10N_SYSTEM_LOCALE),
                [
                    'context' => static::getL10N($this->getContext(), static::L10N_SYSTEM_LOCALE),
                    'details' => $this->getDetails(),
                ]
            ),
            $base_code,
            $previous
        );

        if (
            $this->canShowFe()
            && isset(static::EXCEPTIONS_PROPERTIES[$base_code]['message_fe'])
            && '' !== static::EXCEPTIONS_PROPERTIES[$base_code]['message_fe']
        ) {
            $this->_message_base = (string) static::EXCEPTIONS_PROPERTIES[$base_code]['message_fe'];
        }
        $this->_message_base = static::getL10N($this->getMessageBase());
        $this->_context = static::getL10N($this->getContext());
    }

    /* Getters */

    /**
     * Returns the exception flag if the ::getMessageFe() should return the real message.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the value (the 'show_fe' config property).
     * @see CustomizableException::getMessageFe()           For the frontend message composing algorythm.
     * @see CustomizableException::__construct()            For cases when the the 'show_fe' config property is ignored.
     *
     * @return bool The flag if the exception message can be shown in frontend interfaces.
     */
    public function canShowFe(): bool
    {
        return $this->_show_fe;
    }

    /**
     * Returns the exception context (where it happened; the subject).
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the default value
     *                                                      (the 'context' config property).
     * @see CustomizableException::setContext()             For setting the value during runtime.
     *
     * @return string The exception context.
     */
    public function getContext(): string
    {
        return $this->_context;
    }

    /**
     * Returns the exception details (what exact value is invalid, what is expected etc.).
     *
     * This value is set in the constructor only by being passed as one of its parameters.
     *
     * @see CustomizableException::__construct()    For the exception construction algorythm.
     *
     * @return string The exception details.
     */
    public function getDetails(): string
    {
        return $this->_details;
    }

    /**
     * Returns the exception base message.
     *
     * This message can be equal to a value returned by the Exception::getMessage() and other
     * original finalized Exception methods if no context, details or any other exception message parts are specified.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For configuring the value
     *                                                      (the 'message' and 'message_fe' config properties).
     * @see CustomizableException::__construct()            For the exception construction algorythm where this value
     *                                                      is set depending on circumstances.
     *
     * @return string The exception base message.
     */
    public function getMessageBase(): string
    {
        return $this->_message_base;
    }

    /**
     * Returns the frontend version of the exception message.
     *
     * This method determines if it can return the exception message for frontend interfaces by checking
     * the ::canShowFe() value.
     * If true then the ::getMessageComposed() value is returned (using the ::getMessageBase(), ::getContext()
     * and ::getDetails() returned values).
     * The ::getMessageFeStub() is returned otherwise.
     *
     * @see CustomizableException::canShowFe()          For checking if the exception message can be shown
     *                                                  in frontend interfaces
     * @see CustomizableException::getMessageComposed() For the full exception message composing algorythm.
     * @see CustomizableException::getMessageBase()     For getting the $_message_base property value.
     * @see CustomizableException::getContext()         For getting the $_context property value.
     * @see CustomizableException::getDetails()         For getting the $_details property value.
     * @see CustomizableException::getMessageFeStub()   For the stub composing algorythm.
     *
     * @return string The exception frontend message.
     */
    public function getMessageFe(): string
    {
        if (!$this->canShowFe()) {
            return static::getMessageFeStub($this->getCodeBase());
        }

        return static::getMessageComposed(
            $this->getMessageBase(),
            ['context' => $this->getContext(), 'details' => $this->getDetails()]
        );
    }

    /* /Getters */

    /* Setters */

    /**
     * Sets the new context (where it happened; the subject) for the exception.
     *
     * @see CustomizableException::EXCEPTIONS_PROPERTIES    For setting the default value
     *                                                      (the 'context' config property).
     * @see CustomizableException::getContext()             For getting the value.
     *
     * @param string $value The new context value.
     *
     * @return CustomizableException The updated exception object.
     */
    public function setContext(string $value): CustomizableException
    {
        $this->_context = static::getL10N($value);

        return $this;
    }

    /* /Setters */
}