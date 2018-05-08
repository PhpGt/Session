# Encapsulated user sessions.

This library is a simple object oriented alternative to the $_SESSION superglobal that can be read using the same associative array style code. 

Sessions can be addressed using dot notation, allowing for removing whole categories of session data which is particularly useful to log out a user, for example.



***

<a href="https://circleci.com/gh/PhpGt/Session" target="_blank">
	<img src="https://img.shields.io/circleci/project/PhpGt/Session/master.svg?style=flat-square" alt="Build status" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/Session" target="_blank">
	<img src="https://img.shields.io/scrutinizer/g/PhpGt/Session/master.svg?style=flat-square" alt="Code quality" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/Session" target="_blank">
	<img src="https://img.shields.io/scrutinizer/coverage/g/PhpGt/Session/master.svg?style=flat-square" alt="Code coverage" />
</a>
<a href="https://packagist.org/packages/PhpGt/Session" target="_blank">
	<img src="https://img.shields.io/packagist/v/PhpGt/Session.svg?style=flat-square" alt="Current version" />
</a>
<a href="http://www.php.gt/session" target="_blank">
	<img src="https://img.shields.io/badge/docs-www.php.gt/session-26a5e3.svg?style=flat-square" alt="PHP.G/Session documentation" />
</a>

## Example usage: Welcome a user by their first name or log out the user

```php
if($session->has("user")) {
	if($action === "logout") {
		$session->delete("user");
	}
	else {
		$message = "Welcome back, " . $session->get("user.name.first");
	}
}
else {
	$message = "Please log in";
}
```