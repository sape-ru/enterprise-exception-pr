# Mastering GlobalException

(browse code: [src/GlobalException](../../../../src/GlobalException.php))

Sometimes you need more flexibility with [GlobalException](../dummies/global-exception.md). This article describes some
complicated cases and their possible solutions.

Contents:
- [Inadecuate base code maximum](#inadecuate-base-code-maximum)
- [Global code application limit](#global-code-application-limit)
- [Global exceptions from another world](#global-exceptions-from-another-world)
- [Global codes formatting](#global-codes-formatting)

## Inadecuate base code maximum

Your application starts using an external service, let's call it _ExtApp_. That service can throw exceptions. You
create another [GlobalException](../dummies/global-exception.md) descendant called _ExtAppException_ and configure a
unique _class code_ for it, let it be **29**. So when _ExtApp_ throws an exception **5101** you pass its code as
_base code_ to _ExtAppException_ constructor, get your _global code_ **2905101** and everyone is happy...

_SUDDENLY_ _ExtApp_ throws an exception with its code **325009**. Then bad things happen:
- This code is considered as invalid by `validateCodeBase()` (because it is **not** less than **100000**).
- The exception is not designated as _global_, `getCode()` returns **325009** _as is_.
- The exception's _class code_ is considered equal to **0** when its _global code_ is parsed via `getCodeParts()`.
- [Parser](../dummies/parser.md) throws a validation error.

### Solution

Increase _ExtAppException_ _base code_ maximum.

[GlobalException](../dummies/global-exception.md) has `CLASS_CODE_MULTIPLIER` constant which is used for any validation
or calculation concerning _base codes_. Initially its value equals to **100000** - every _base code_ must be less than
this value to be considered as valid.

You can redefine this constant for _ExtAppException_ class. Firstly predict the maximum possible code thrown by
_ExtApp_; let's imagine it is something around **99999999** (**8** digits). Your next move is to redefine
`CLASS_CODE_MULTIPLIER` in _ExtAppException_ with **8** power of ten:

```php
class ExtAppException extends MyAppBaseException
{
    const CLASS_CODE_MULTIPLIER = 10 ** 8;
    // ...
}
```

That's it! You don't even need to change _ExtAppException_ _class code_! From this point if _ExtApp_ throws the
exception **325009** your _ExtAppException_ will successfully calculate the _global code_ **2900325009**.

You should also take into consideration that from this point if you define another exception class with the same _class
code_ **29** it will be considered as duplicate only if that new class has the same `CLASS_CODE_MULTIPLIER` as
_ExtAppException_. Otherwise it is guaranteed (and [Parser](../dummies/parser.md) can prove it) that you will have no
_global codes_ duplicates throughout these two classes:

```php
class ExtAppException extends MyAppBaseException
{
    const CLASS_CODE_MULTIPLIER = 10 ** 8;
}

class AnotherException extends MyAppBaseException
{
}

class MyAppBaseException extends GlobalException
{
    const CLASS_CODE_LIST = [
        ExtAppException::class  => 29, // CLASS_CODE_MULTIPLIER = 10 ** 8;
        AnotherException::class => 29, // CLASS_CODE_MULTIPLIER = 10 ** 5;
    ];
}

echo ExtAppException::getCodeGlobal(1) . "\n";  // >> 2900000001
echo AnotherException::getCodeGlobal(1) . "\n"; // >> 2900001
```

At the same time and for the same reason you should **not** set _AnotherException_ _class code_ to **29000**. Otherwise
it is possible to generate _global codes_ duplicates (and [Parser](../dummies/parser.md) will throw an exception for
this case).

## Global code application limit

An exception valid _global code_ is limited by valid _base code_ and _class code_. You should already know the
[real _base code_ condition](#inadecuate-base-code-maximum) and how to alter it.

A _class code_ is validated by `validateCodeClass()` - it must be positive (or equal to **0** for disabling the
globalization feature) but less than the value returned by `getCodeClassMax()`. This condition guarantee that a
possible _global code_ maximum will not exceed PHP integer maximum. But sometimes you need to set much smaller limit.

For instance if your aplication has its own API based on [XML-RPC protocol](http://xmlrpc.scripting.com/spec.html)
your API capabilites are limited: _faultCode_ (and any other integer values) must be of type 32-bit signed integer -
_int8_ is not supported by the protocol. So you need to ensure your _global codes_ will _not_ exceed 32-bit signed
integer.

### Solution

`getCodeClassMax()` is finalized intentionally. Change `GLOBAL_CODE_MAX_RELATIVE` instead. Initially its value equals
to `PHP_INT_MAX` but you can change it to fit your application limits. For the example described above you should set
the limit to 32-bit signed integer maximum:

```php
// All your exceptions classes used in your application API must extend this class:
class MyAppOrAPIBaseException extends GlobalException
{
    // ...
    const GLOBAL_CODE_MAX_RELATIVE = 2 ** 31 - 1; // 2147483647
    // ...
}
```

Then if you don't alter `CLASS_CODE_MULTIPLIER` your _class code_ maximum will be **21473**. If you throw an
exception with the _base code_ **99999** you will get the _global code_ **2147399999** wich is definitely less than
32-bit signed integer maximum (**2147483647**).

## Global exceptions from another world

It's great when only your application uses [GlobalException](../dummies/global-exception.md)! But when your partner
external application API starts using the same globalization library at some moment (depending on your
`CLASS_CODE_MULTIPLIER` and `GLOBAL_CODE_MAX_RELATIVE` values) you can not treat that external API exceptions codes
as _base codes_. You must treat those as _global codes_ and create your
[GlobalException](../dummies/global-exception.md) objects accordingly (especially if you also use
[CustomizableException](../dummies/customizable-exception.md) functionality).

### Solution

Let's imagine a scenario when you can be certain about the _class codes_ that external application API uses to generate
its global exceptions. For instance the API can return codes in range from **1100001** to **1599999** (_class codes_
range from **11** to **15**, the same `CLASS_CODE_MULTIPLIER` is equal to **100000**).

1. Create five new exceptions classes (for each possible _class code_), update your `CLASS_CODE_LIST` accordingly by
adding five new _class codes_.
1. Generate a flipped version of your `CLASS_CODE_LIST` so you can find a class name by its _class code_.
1. Decompile incoming _global codes_ from the external API via `getCodeParts()` - this method returns an array with
_class_ and _base_ codes separated.
1. Construct your global exception - pick a class name by the extracted _class code_ and call its constructor passing
the extracted _base code_.

### The worst scenario

Just imagine if two or more external API start using [GlobalException](../dummies/global-exception.md) and can
return identical _global codes_ with different meanings. And you can not convince any of their developers to change
_class codes_. Also you can't afford treating those codes as _base_ because of those integers size.

For now there is no solution [GlobalException](../dummies/global-exception.md) can provide you with... But maybe
**you** can suggest a nice strategy or the library improvement!

## Global codes formatting

If you want to print a formatted _global code_ then redefine and use `getCodeFormatted()` static method.

Initially this method returns just a _global code_ itself and accepts a _base code_ as the only parameter. Also this
method is called in `CustomizableException::getMessageDefault()` and `CustomizableException::getMessageFeStub()`
methods (read [Mastering CustomizableException]() for more info).

## Further reading

- [GlobalException basics](../dummies/global-exception.md)
- [Mastering CustomizableException]()
- [Mastering Parser]()