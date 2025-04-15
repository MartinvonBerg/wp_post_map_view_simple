// eslint.config.js
import { defineConfig } from "eslint/config";
import security from 'eslint-plugin-security';

export default defineConfig([
	{
		languageOptions: {
			ecmaVersion: "latest",
			sourceType: "module",
			globals: {
				L: "readonly",
				module: "readonly",
				define: "readonly",
				require: "readonly",
				window: "readonly",
				document: "readonly"
			}
		},
		plugins: {
			security
		},
		rules: {
			camelcase: "off",
			quotes: ["error", "single", { avoidEscape: true }],
			"no-mixed-spaces-and-tabs": ["error", "smart-tabs"],
			"space-before-function-paren": ["error", "always"],
			"space-in-parens": ["error", "never"],
			"object-curly-spacing": "off", //["error", "never"],
			"array-bracket-spacing": ["error", "never"],
			"computed-property-spacing": ["error", "never"],
			"space-before-blocks": "error",
			"keyword-spacing": "error",
			"no-lonely-if": "error",
			"comma-style": ["error", "last"],
			"no-underscore-dangle": "off",
			"no-constant-condition": "off",
			"no-multi-spaces": "off",
			"strict": "off",
			"key-spacing": "off",
			"no-shadow": "off",
			"no-unused-vars": ["error", { vars: "all", args: "after-used", ignoreRestSiblings: true }],
			//...security.configs.recommended.rules, // aktiviert empfohlene Sicherheitsregeln
			/*
			"security/detect-object-injection": "off",
			"security/detect-eval-with-expression": "error",
			"security/detect-pseudoRandomBytes": "error",
  			"security/detect-buffer-noassert": "error",
			"security/detect-non-literal-fs-filename": "error",
			"security/detect-non-literal-regexp": "error",
			"security/detect-child-process": "error",
			"security/detect-new-buffer": "error",
			"security/detect-unsafe-regex": "error",
			"security/detect-buffer-noassert": "error",
			"security/detect-non-literal-require": "error",
			*/
		}
	}
]);
