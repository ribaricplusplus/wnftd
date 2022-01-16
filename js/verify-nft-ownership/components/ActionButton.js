import { Button, Link } from '@mui/material';
import { LoadingButton } from '@mui/lab';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect, useRegistry } from '@wordpress/data';

import { STORE_NAME } from '../store';

export default function ActionButton( { status, setStatus } ) {
	const { userOwnedPublicAddresses } = useSelect(
		( select ) => ( {
			userOwnedPublicAddresses: select(
				STORE_NAME
			).getUserOwnedPublicAddresses(),
		} ),
		[]
	);
	const { getPublicAddress } = useSelect( STORE_NAME );
	const {
		requestPublicAddressVerification,
		requestProductDownload,
		clearMessages,
		addMessage,
	} = useDispatch( STORE_NAME );
	const registry = useRegistry();

	const clickHandler = async () => {
		await clearMessages();
		const publicAddress = await registry
			.resolveSelect( STORE_NAME )
			.getPublicAddress();

		try {
			if ( userOwnedPublicAddresses.includes( publicAddress ) ) {
				setStatus( { ...status, state: 'loading' } );

				await requestProductDownload();

				await addMessage( {
					name: 'nft_verification_succeeded',
					message: __( 'NFT ownership verified.' ),
					severity: 'success',
				} );

				setStatus( { ...status, state: 'verified' } );
			} else if ( publicAddress ) {
				setStatus( { ...status, state: 'loading' } );

				await requestPublicAddressVerification();

				await requestProductDownload();

				await addMessage( {
					name: 'nft_verification_succeeded',
					message: __( 'NFT ownership verified.' ),
					severity: 'success',
				} );

				setStatus( { ...status, state: 'verified' } );
			}
		} catch ( e ) {
			setStatus( { ...status, state: 'error' } );
			await handleError( e, addMessage );
		}
	};

	if ( status.state === 'verified' ) {
		const url = window?.wnftdData?.downloadsUrl;
		return (
			<Button href={ url } variant="contained" color="success">
				View downloads
			</Button>
		);
	} else {
		const isLoading = status.state === 'loading';

		return (
			<LoadingButton
				loading={ isLoading }
				onClick={ isLoading ? null : clickHandler }
				variant="contained"
			>
				Verify ownership
			</LoadingButton>
		);
	}
}

async function handleError( e, addMessage ) {
	if ( e.code === 'signature_verification' ) {
		await addMessage( {
			name: 'public_address_verification_failed',
			message: __( 'Signature verification failed.', 'wnftd' ),
			severity: 'error',
		} );
	} else if ( e.code === 'nft_verification_failed' ) {
		await addMessage( {
			name: 'nft_verification_failed',
			message: __( 'NFT Ownership verification failed.', 'wnftd' ),
			severity: 'error',
		} );
	} else {
		await addMessage( {
			name: 'error_occurred',
			message: __( 'An error occurred.', 'wnftd' ),
			severity: 'error',
		} );
	}
}
