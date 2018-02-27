<?php
namespace Componere {
	class Instrumental {
		public function __construct(string $target) {
			$this->definition = 
				new Definition($target);
			$this->methods = $this->definition->getClosures();
		}

		public function addPrecondition(string $method, \Closure $function) {
			if ($this->instrumented)
				throw new \RuntimeException("cannot add pre condition after instrumentation");
			$this->conditions[$method]["precondition"][] = $function;
		}

		public function addPostcondition(string $method, \Closure $function) {
			if ($this->instrumented)
				throw new \RuntimeException("cannot add post condition after instrumentation");
			$this->conditions[$method]["postcondition"][] = $function;
		}

		public function addInvariant(\Closure $function) {
			if ($this->instrumented)
				throw new \RuntimeException("cannot add invariant after instrumentation");
			$this->invariants[] = $function;
		}

		public function addOverride($name, \Closure $method) {
			if ($this->instrumented)
				throw new \RuntimeException("cannot add override after instrumentation");
			$this->methods[$name] = $method;
		}

		public function instrument() {
			if ($this->instrumented)
				throw new \RuntimeException("already instrumented");

			$invariants = $this->invariants;

			foreach ($this->methods as $name => $closure) {	
				$conditions = $this->conditions[$name];

				$this->definition->addMethod($name, new Method(function(...$args) use($closure, $conditions, $invariants) {
					foreach ($conditions["precondition"] ?: [] as $precondition)
						\Closure::bind($precondition, $this, self::class)(...$args);
				
					$return = \Closure::bind($closure, $this, self::class)(...$args);

					foreach ($conditions["postcondition"] ?: [] as $postcondition)
						\Closure::bind($postcondition, $this, self::class)($return, ...$args);

					foreach ($invariants ?: [] as $invariant)
						\Closure::bind($invariant, $this, self::class)();

					return $return;
				}));
			}
			$this->definition->register();
			$this->instrumented = true;
		}

		private $definition;
		private $methods;
		private $conditions;
		private $invariants;
		private $instrumented = false;
	}
}
