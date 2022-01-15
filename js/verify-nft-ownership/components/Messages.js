import { useSelect } from '@wordpress/data';
import { Alert } from '@mui/material';

import { STORE_NAME } from '../store';

export default function Messages() {
	const { messages } = useSelect(
		( select ) => ( {
			messages: select( STORE_NAME ).getMessages(),
		} ),
		[]
	);

	return (
		<>
			{ messages.map( ( message ) => (
				<Alert key={ message.name } severity={ message.severity }>
					{ message.message }
				</Alert>
			) ) }
		</>
	);
}
