/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	// Static JSON data
	const programData = [
		{
			id: 123,
			major: 'Accounting',
			degrees: [
				{ name: 'MACC', url: 'https://haslam.utk.edu/accounting/macc/' }
			],
			concentrations: [
				{ name: 'Audit and Controls', online: true },
				{ name: 'Information Management', online: false },
				{ name: 'Taxation', online: false },
			],
		},
		{
			id: 124,
			major: 'Accounting',
			degrees: [
				{ name: 'BSBA', url: 'https://haslam.utk.edu/accounting/bachelors/' }
			],
			concentrations: [
				{ name: 'Collateral Option', online: false },
				{ name: 'Integrated Business and Engineering', online: false },
				{ name: 'International Business', online: false },
			],
		},
	];

	return (
		<>
			<div className="wp-block-block alignfull utkwds-orange-bar-texture has-orange-background-color has-background" />
			<section { ...useBlockProps() } className="programs-filters alignwide" id="filters">
				{ __(
					<>
						<div className="programs-filters-fields">
							<div className="programs-filters-field">
								<div class="form-floating">
									<input className='form-control' aria-label="Program Search" id="program-search" name="search" type="search" placeholder="Find a program" />
									<label for="program-search">Find a Program</label>
								</div>
							</div>
							<div className="programs-filters-field">
								<div class="form-floating">
									<select name="degree-type" className="form-select" id="degree-type" aria-label="Degree Type">
										<option value="">Select a Degree</option>
									</select>
									<label for="degree-type">Degree Type</label>
								</div>
							</div>
							<div className="programs-filters-field">
								<div class="form-floating">
									<select name="area" className="form-select" id="area-of-study" aria-label="Area of Study">
										<option value="">Select an Area of Study</option>
									</select>
									<label for="area-of-study">Area of Study</label>
								</div>
							</div>
							<div className="programs-filters-field">
								<div class="form-floating">
									<select name="college" className="form-select" id="college" aria-label="College">
										<option value="">Select a College</option>
									</select>
									<label for="college">College</label>
								</div>
							</div>
							<div className="programs-filters-field">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" />
								<label class="form-check-label" for="flexSwitchCheckDefault">Online</label>
							</div>
							</div>
						</div>	
						<div className="programs-filters-results" id="program-results">
							<div className="programs-filters-headings">
								<h2 className="programs-filters-heading">Program</h2>
								<h2 className="programs-filters-heading">Degree / Certificate</h2>
								<h2 className="programs-filters-heading">Concentration</h2>
							</div>
							{programData.map((program) => (
								<div key={program.id} className="program-entry">
									<div className="program-entry-block">
										<p className="program-entry-label">Program</p>
										<p className="program-entry-text program-entry-text--bold">{program.major}</p>
									</div>
									<div className="program-entry-block">
										<p className="program-entry-label">Degree / Certificate</p>
										<ul className="degree-list">
											{program.degrees.map((degree, index) => (
												<li key={index} className="program-entry-text program-entry-text--bold">
													{degree.name}
												</li>
											))}
										</ul>							
									</div>
									<div className="program-entry-block">
										<p className="program-entry-label">Concentrations</p>
										<ul className="concentration-list">
											{program.concentrations.map((concentration, index) => (
												<li key={index} className="program-entry-text">
													{concentration.name}{' '}
													{concentration.online && (
														<span className="online-tag">Online</span>
													)}
												</li>
											))}
										</ul>						
									</div>
								</div>
							))}
						</div>
					</>
				) }
			</section>
		</>
	);
}
