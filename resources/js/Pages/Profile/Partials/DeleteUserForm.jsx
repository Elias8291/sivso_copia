import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';

const dangerBtn =
    'rounded-lg bg-red-600 px-5 py-2.5 text-[11px] font-bold uppercase tracking-wider hover:bg-red-500 focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-900';

const secondaryBtn =
    'rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-zinc-600 hover:bg-zinc-50 focus:ring-2 focus:ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700';

const labelClass = '!text-[11px] !font-bold !uppercase !tracking-wider !text-zinc-500 dark:!text-zinc-400';

export default function DeleteUserForm({ inputClassName = '' }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef();

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);

        clearErrors();
        reset();
    };

    return (
        <section className="w-full space-y-6">
            <header className="border-b border-red-200/50 pb-5 dark:border-red-900/30">
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-red-600/90 dark:text-red-400">
                    Zona crítica
                </p>
                <h2 className="mt-1 text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Eliminar cuenta
                </h2>
                <p className="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                    Al eliminarla se borrarán tus datos en este sistema. Descarga antes lo que necesites conservar.
                </p>
            </header>

            <DangerButton onClick={confirmUserDeletion} className={dangerBtn}>
                Eliminar mi cuenta
            </DangerButton>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form
                    onSubmit={deleteUser}
                    className="border-t-4 border-red-500 bg-white p-6 dark:border-red-600 dark:bg-zinc-950 sm:p-8"
                >
                    <h2 className="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        ¿Eliminar la cuenta de forma permanente?
                    </h2>

                    <p className="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Esta acción no se puede deshacer. Escribe tu contraseña para confirmar.
                    </p>

                    <div className="mt-6">
                        <InputLabel
                            htmlFor="delete_password"
                            value="Contraseña"
                            className={labelClass}
                        />

                        <TextInput
                            id="delete_password"
                            type="password"
                            name="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                            className={`${inputClassName} !w-full sm:!max-w-md`}
                            isFocused
                            placeholder="••••••••"
                            autoComplete="current-password"
                        />

                        <InputError
                            message={errors.password}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-8 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
                        <SecondaryButton type="button" onClick={closeModal} className={secondaryBtn}>
                            Cancelar
                        </SecondaryButton>

                        <DangerButton className={dangerBtn} disabled={processing}>
                            Sí, eliminar
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
