import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-zinc-900 dark:text-zinc-100">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="mx-auto w-full max-w-[1600px]">
                <div className="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white/90 shadow-sm backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-900/50">
                    <div className="p-6 text-zinc-700 dark:text-zinc-300">
                        Sesión iniciada correctamente.
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
