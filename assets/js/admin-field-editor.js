/**
 * Regex Validation for Gravity Forms - Admin Field Editor
 *
 * Handles the field editor UI for regex validation settings in the Gravity Forms form builder.
 * Supports both simple mode (single pattern) and advanced mode (per-input patterns).
 */
( function ( $ ) {
	'use strict';

	// Cache reference to global config object
	const config = window.gfRegexValidation || {};

	// Wait for DOM to be ready before modifying fieldSettings
	$( document ).ready( function () {
		const fieldTypes = config.fieldTypes || [];

		fieldTypes.forEach( function ( type ) {
			if (
				typeof fieldSettings !== 'undefined' &&
				typeof fieldSettings[ type ] !== 'undefined'
			) {
				fieldSettings[ type ] += ', .regex_validation_setting';
			}
		} );
	} );

	// Initialize when field settings are loaded
	$( document ).on( 'gform_load_field_settings', function ( event, field ) {
		// Check if field has advanced mode patterns
		const hasAdvancedPatterns =
			field.regexPatterns && typeof field.regexPatterns === 'object';

		if ( hasAdvancedPatterns ) {
			// Load advanced mode
			$( '#regex_mode_advanced' ).prop( 'checked', true );
			SetRegexMode( 'advanced' );
			loadAdvancedPatterns( field );
		} else {
			// Load simple mode
			$( '#regex_mode_simple' ).prop( 'checked', true );
			SetRegexMode( 'simple' );
			$( '#field_regex_pattern' ).val( field.regexPattern || '' );
			$( '#field_regex_message' ).val( field.regexMessage || '' );

			// Set preset dropdown
			const presets = config.presets || {};
			let selectedPreset = '';

			for ( const [ key, preset ] of Object.entries( presets ) ) {
				if ( field.regexPattern === preset.pattern ) {
					selectedPreset = key;
					break;
				}
			}

			$( '#field_regex_preset' ).val( selectedPreset );
		}

		// Populate advanced mode UI with available inputs
		populateAdvancedInputs( field );
	} );

	/**
	 * Get suggested preset based on field configuration
	 */
	function getSuggestedPreset( field ) {
		if ( ! field ) {
			return null;
		}

		// For Name fields, suggest the standard preset
		if ( field.type === 'name' ) {
			return 'name_standard';
		}

		// For Address fields, suggest based on addressType
		if ( field.type === 'address' ) {
			const addressType = field.addressType || 'international';

			// Map address types to presets
			const presetMap = {
				us: 'address_us_complete',
				canadian: 'address_canadian',
			};

			// For international, check WordPress locale for German
			if ( addressType === 'international' ) {
				// Try to detect German locale from WordPress
				const locale =
					window.gf_vars?.gf_currency_config?.code ||
					navigator.language ||
					'';
				if ( locale.startsWith( 'de' ) ) {
					return 'address_german';
				}
				// Default to international preset
				return 'address_international';
			}

			return presetMap[ addressType ] || 'address_international';
		}

		return null;
	}

	/**
	 * Switch between simple and advanced modes
	 */
	window.SetRegexMode = function ( mode ) {
		const field = GetSelectedField();

		if ( mode === 'simple' ) {
			$( '#regex_simple_mode_container' ).show();
			$( '#regex_advanced_mode_container' ).hide();

			// Clear advanced patterns
			SetFieldProperty( 'regexPatterns', null );
			$( '#field_regex_patterns' ).val( '' );
		} else {
			$( '#regex_simple_mode_container' ).hide();
			$( '#regex_advanced_mode_container' ).show();

			// Clear simple mode values
			SetFieldProperty( 'regexPattern', '' );
			SetFieldProperty( 'regexMessage', '' );

			// Populate inputs if not already done
			if (
				$( '#regex_advanced_inputs_container' ).children().length === 0
			) {
				// Auto-select and apply preset based on field configuration
				// Only if the field doesn't already have patterns configured
				const hasExistingPatterns =
					field.regexPatterns &&
					Object.keys( field.regexPatterns ).length > 0;

				if ( ! hasExistingPatterns ) {
					const suggestedPreset = getSuggestedPreset( field );
					if ( suggestedPreset ) {
						$( '#field_regex_advanced_preset' ).val(
							suggestedPreset
						);
						// Auto-apply the preset
						ApplyAdvancedPreset( suggestedPreset );
					}
				} else {
					// Field has existing patterns, just populate them
					populateAdvancedInputs( field );
				}
			}
		}
	};

	/**
	 * Set simple mode preset values
	 */
	window.SetRegexPreset = function ( presetKey ) {
		if ( ! presetKey ) {
			return;
		}

		// Check if it's a compound preset
		if ( presetKey.startsWith( 'compound_' ) ) {
			const actualKey = presetKey.replace( 'compound_', '' );
			const compoundPresets = config.compoundPresets || {};
			const preset = compoundPresets[ actualKey ];

			if ( preset ) {
				// Switch to advanced mode and apply the preset
				$( '#regex_mode_advanced' ).prop( 'checked', true );
				SetRegexMode( 'advanced' );
				$( '#field_regex_advanced_preset' ).val( actualKey );
				ApplyAdvancedPreset( actualKey );
			}
			return;
		}

		// Regular single-field preset
		const presets = config.presets || {};
		const preset = presets[ presetKey ];

		if ( preset ) {
			SetFieldProperty( 'regexPattern', preset.pattern );
			SetFieldProperty( 'regexMessage', preset.message );
			$( '#field_regex_pattern' ).val( preset.pattern );
			$( '#field_regex_message' ).val( preset.message );
		}
	};

	/**
	 * Apply an advanced mode preset to all inputs
	 */
	window.ApplyAdvancedPreset = function ( presetKey ) {
		if ( ! presetKey ) {
			return;
		}

		const compoundPresets = config.compoundPresets || {};
		const preset = compoundPresets[ presetKey ];

		if ( ! preset || ! preset.patterns ) {
			return;
		}

		const field = GetSelectedField();
		if ( ! field ) {
			return;
		}

		// Clear existing inputs container
		$( '#regex_advanced_inputs_container' ).empty();

		// Apply patterns from preset
		const patterns = preset.patterns;
		for ( const [ inputNumber, inputConfig ] of Object.entries(
			patterns
		) ) {
			addAdvancedInputRow(
				field,
				inputNumber,
				inputConfig.pattern,
				inputConfig.message
			);
		}

		// Save to field
		saveAdvancedPatterns();
	};

	/**
	 * Populate the advanced inputs container based on field type
	 */
	function populateAdvancedInputs( field ) {
		if ( ! field ) {
			return;
		}

		const container = $( '#regex_advanced_inputs_container' );
		container.empty();

		// Get available inputs for this field type
		let inputStructure = {};

		if ( field.type === 'name' ) {
			inputStructure = config.nameInputs || {};
		} else if ( field.type === 'address' ) {
			inputStructure = config.addressInputs || {};
		}

		// If no inputs structure or field has existing patterns, show them
		const existingPatterns = field.regexPatterns || {};

		if (
			Object.keys( existingPatterns ).length > 0 ||
			Object.keys( inputStructure ).length === 0
		) {
			// Show existing patterns or empty state
			for ( const [ inputNumber, inputConfig ] of Object.entries(
				existingPatterns
			) ) {
				const inputLabel = getInputLabel(
					field,
					inputNumber,
					inputStructure
				);
				addAdvancedInputRow(
					field,
					inputNumber,
					inputConfig.pattern,
					inputConfig.message,
					inputLabel
				);
			}
		}
	}

	/**
	 * Load advanced patterns into the UI
	 */
	function loadAdvancedPatterns( field ) {
		const patterns = field.regexPatterns || {};
		const container = $( '#regex_advanced_inputs_container' );
		container.empty();

		for ( const [ inputNumber, inputConfig ] of Object.entries(
			patterns
		) ) {
			addAdvancedInputRow(
				field,
				inputNumber,
				inputConfig.pattern,
				inputConfig.message
			);
		}
	}

	/**
	 * Get the label for a specific input
	 */
	function getInputLabel( field, inputNumber, inputStructure ) {
		// Try to get from the inputStructure
		if ( inputStructure && inputStructure[ inputNumber ] ) {
			return inputStructure[ inputNumber ];
		}

		// Try to get from field inputs array
		if ( field.inputs && Array.isArray( field.inputs ) ) {
			for ( const input of field.inputs ) {
				const inputId = String( input.id || '' );
				const parts = inputId.split( '.' );
				const currentNumber = parts[ parts.length - 1 ];

				if ( currentNumber === inputNumber ) {
					return (
						input.customLabel ||
						input.label ||
						`Input ${ inputNumber }`
					);
				}
			}
		}

		return `Input ${ inputNumber }`;
	}

	/**
	 * Check if an input is hidden in the field configuration
	 */
	function isInputHidden( field, inputNumber ) {
		if ( ! field.inputs || ! Array.isArray( field.inputs ) ) {
			return false;
		}

		for ( const input of field.inputs ) {
			const inputId = String( input.id || '' );
			const parts = inputId.split( '.' );
			const currentNumber = parts[ parts.length - 1 ];

			if ( currentNumber === inputNumber ) {
				return input.isHidden === true;
			}
		}

		return false;
	}

	/**
	 * Add an input configuration row to the advanced mode UI
	 */
	function addAdvancedInputRow(
		field,
		inputNumber,
		pattern,
		message,
		customLabel
	) {
		pattern = pattern || '';
		message = message || '';

		// Get input label
		let inputStructure = {};
		if ( field.type === 'name' ) {
			inputStructure = config.nameInputs || {};
		} else if ( field.type === 'address' ) {
			inputStructure = config.addressInputs || {};
		}

		const inputLabel =
			customLabel || getInputLabel( field, inputNumber, inputStructure );
		const isHidden = isInputHidden( field, inputNumber );
		const hiddenClass = isHidden ? ' regex-input-hidden' : '';
		const hiddenLabel = isHidden ? ' (hidden in form)' : '';

		const inputPresets = config.inputPresets || {};

		const row = $( '<div>' )
			.addClass( 'regex-advanced-input-row' + hiddenClass )
			.attr( 'data-input-number', inputNumber )
			.css( {
				marginBottom: '15px',
				padding: '15px',
				border: '1px solid #c3c4c7',
				borderRadius: '3px',
				backgroundColor: isHidden ? '#f6f7f7' : '#fff',
			} );

		const header = $( '<div>' )
			.css( {
				display: 'flex',
				justifyContent: 'space-between',
				alignItems: 'center',
				marginBottom: '10px',
			} )
			.append( $( '<strong>' ).text( inputLabel + hiddenLabel + ':' ) )
			.append(
				$( '<button>' )
					.attr( 'type', 'button' )
					.addClass( 'button-link-delete' )
					.text( 'Remove' )
					.on( 'click', function () {
						row.remove();
						saveAdvancedPatterns();
					} )
			);

		const presetSelect = $( '<select>' )
			.addClass( 'input-preset-select' )
			.css( { width: '100%', marginBottom: '8px' } )
			.append(
				$( '<option>' )
					.val( '' )
					.text( '-- Select a preset pattern --' )
			);

		for ( const [ key, preset ] of Object.entries( inputPresets ) ) {
			presetSelect.append(
				$( '<option>' ).val( key ).text( preset.label )
			);
		}

		presetSelect.on( 'change', function () {
			const selectedKey = $( this ).val();
			if ( selectedKey && inputPresets[ selectedKey ] ) {
				row.find( '.input-pattern' ).val(
					inputPresets[ selectedKey ].pattern
				);
				row.find( '.input-message' ).val(
					inputPresets[ selectedKey ].message
				);
				saveAdvancedPatterns();
			}
		} );

		const patternInput = $( '<input>' )
			.attr( 'type', 'text' )
			.addClass( 'input-pattern' )
			.css( { width: '100%', marginBottom: '8px' } )
			.attr( 'placeholder', '/^[\\p{L}\\s]+$/u' )
			.val( pattern )
			.on( 'keyup', saveAdvancedPatterns );

		const messageInput = $( '<input>' )
			.attr( 'type', 'text' )
			.addClass( 'input-message' )
			.css( { width: '100%' } )
			.attr( 'placeholder', 'Please enter a valid value' )
			.val( message )
			.on( 'keyup', saveAdvancedPatterns );

		row.append( header )
			.append(
				$( '<label>' )
					.css( {
						display: 'block',
						marginBottom: '5px',
						marginTop: '5px',
						fontWeight: '600',
					} )
					.text( 'Quick Preset:' )
			)
			.append( presetSelect )
			.append(
				$( '<label>' )
					.css( {
						display: 'block',
						marginBottom: '5px',
						marginTop: '10px',
						fontWeight: '600',
					} )
					.text( 'Pattern:' )
			)
			.append( patternInput )
			.append(
				$( '<label>' )
					.css( {
						display: 'block',
						marginBottom: '5px',
						marginTop: '10px',
						fontWeight: '600',
					} )
					.text( 'Message:' )
			)
			.append( messageInput );

		$( '#regex_advanced_inputs_container' ).append( row );
	}

	/**
	 * Add a new input pattern row
	 */
	window.AddAdvancedInput = function () {
		const field = GetSelectedField();
		if ( ! field ) {
			return;
		}

		// Prompt for input number
		const inputNumber = prompt(
			'Enter the input number (e.g., "1" for street address, "3" for city):'
		);

		if ( ! inputNumber ) {
			return;
		}

		addAdvancedInputRow( field, inputNumber.trim(), '', '' );
	};

	/**
	 * Save advanced patterns to the field
	 */
	function saveAdvancedPatterns() {
		const patterns = {};
		let hasPatterns = false;

		$( '.regex-advanced-input-row' ).each( function () {
			const inputNumber = $( this ).attr( 'data-input-number' );
			const pattern = $( this ).find( '.input-pattern' ).val().trim();
			const message = $( this ).find( '.input-message' ).val().trim();

			if ( pattern ) {
				patterns[ inputNumber ] = {
					pattern: pattern,
					message: message || 'Please enter a valid value',
				};
				hasPatterns = true;
			}
		} );

		if ( hasPatterns ) {
			SetFieldProperty( 'regexPatterns', patterns );
			$( '#field_regex_patterns' ).val( JSON.stringify( patterns ) );
		} else {
			SetFieldProperty( 'regexPatterns', null );
			$( '#field_regex_patterns' ).val( '' );
		}
	}
} )( jQuery );
