Componere\Instrumental
======================
*Instrumenting classes since 2018*

This package allows the instrumentation of classes using Componere

API
===

```
namespace Componere {
	class Instrumental {
		public function __construct(string $target);
		public function addOverride(string $method, \Closure $override);
		public function addInvariant(\Closure $handler);
		public function addPrecondition(string $method, \Closure $handler);
		public function addPostcondition(string $method, \Closure $handler);
		public function instrument();
	}
}
```
